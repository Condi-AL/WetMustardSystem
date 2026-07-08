<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Batch Process Parameter (scope entity: BatchProcessParameter).
 *
 * Recipe-specific parameter values captured during a batch (e.g. temperature,
 * time, pH). Optionally linked to a specific process step.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_process_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_record_id')->constrained('batch_records')->cascadeOnDelete();
            // NO ACTION on step link avoids a second cascade path to batch_records.
            $table->foreignId('batch_process_step_id')->nullable()->constrained('batch_process_steps');
            $table->string('parameter_name');
            $table->string('value')->nullable();
            $table->string('uom')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users');
            $table->dateTime('entered_at')->nullable();
            $table->timestamps();

            $table->index('batch_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_process_parameters');
    }
};
