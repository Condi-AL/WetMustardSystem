<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Report Recipient mapping (scope entity: ReportRecipient).
 *
 * report_key NULL = global recipient (all reports). recipient_type is
 * 'direct' (explicit email) or 'role' (resolved from the DBMTS role model).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('report_key')->nullable();      // NULL = global
            $table->string('recipient_type')->default('direct'); // direct | role
            $table->string('recipient_email')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('role_key')->nullable();
            $table->boolean('is_cc')->default(false);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->index('report_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_recipients');
    }
};
