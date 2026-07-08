<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Electronic Signature (scope entity: ElectronicSignature).
 *
 * Captures a named, timestamped signature against any entity for compliance-
 * critical actions (weighed, tipped, step complete, batch complete). Required
 * signatures cannot be blank (scope §11 validation).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electronic_signatures', function (Blueprint $table) {
            $table->id();
            $table->string('entity_name');           // e.g. batch_ingredient_lot, batch_record
            $table->unsignedBigInteger('entity_id');
            $table->string('signature_purpose');     // weighed | tipped | step_complete | batch_complete
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('signed_at');
            $table->string('meaning');               // human-readable meaning of the signature
            $table->string('comment', 500)->nullable();
            $table->timestamps();

            $table->index(['entity_name', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electronic_signatures');
    }
};
