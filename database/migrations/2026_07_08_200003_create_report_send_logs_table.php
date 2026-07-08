<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Report Send Log (scope entity: ReportSendLog). Every scheduled, manual
 * or alert-driven send creates a row (scope §11 validation).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_send_logs', function (Blueprint $table) {
            $table->id();
            $table->string('report_key');
            $table->string('trigger_mode');       // scheduled | manual | per_day
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->text('recipients_to')->nullable();
            $table->text('recipients_cc')->nullable();
            $table->string('status')->default('running'); // running | sent | skipped | failed
            $table->text('error_message')->nullable();
            $table->integer('row_count')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users'); // NO ACTION
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['report_key', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_send_logs');
    }
};
