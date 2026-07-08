<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Batch Process Step (scope entity: BatchProcessStep).
 *
 * A process/mixing/milling step completed against a batch, with mandatory
 * sign-off where required.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_process_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_record_id')->constrained('batch_records')->cascadeOnDelete();
            $table->string('step_name');
            $table->integer('sequence')->nullable();
            $table->boolean('required_flag')->default(true);
            $table->foreignId('completed_by')->nullable()->constrained('users');
            $table->dateTime('completed_at')->nullable();
            $table->string('comments', 500)->nullable();
            $table->timestamps();

            $table->index('batch_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_process_steps');
    }
};
