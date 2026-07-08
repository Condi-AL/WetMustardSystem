<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Packing Hourly Check (scope entity: PackingHourlyCheck).
 * Hygiene/quality checks completed hourly during a packing run.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_hourly_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_run_id')->constrained('packing_runs')->cascadeOnDelete();
            $table->dateTime('check_time');
            $table->boolean('bucket_clean')->default(false);
            $table->boolean('lid_clean')->default(false);
            $table->boolean('lids_secure')->default(false);
            $table->boolean('tamper_in_place')->default(false);
            $table->boolean('label_correct')->default(false);
            $table->boolean('print_clear')->default(false);
            $table->boolean('lot_code_correct')->default(false);
            $table->boolean('filler_clean')->default(false);
            $table->boolean('fill_clean')->default(false);
            $table->foreignId('signed_by')->constrained('users'); // NO ACTION
            $table->dateTime('signed_at');
            $table->timestamps();

            $table->index('packing_run_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_hourly_checks');
    }
};
