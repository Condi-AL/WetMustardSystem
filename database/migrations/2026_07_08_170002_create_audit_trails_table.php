<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Audit Trail (scope entity: AuditTrail).
 *
 * Records state changes and corrections against any entity. Corrections require
 * old value, new value, user, timestamp and reason (scope §11 validation).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->string('entity_name');
            $table->unsignedBigInteger('entity_id');
            $table->string('field_name')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('action');                // create | update | sign | complete | correct
            $table->string('reason', 500)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['entity_name', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
