<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Packing Weight Check (scope entity: PackingWeightCheck).
 * Six sample weights with derived average and pass/fail result.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_weight_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_run_id')->constrained('packing_runs')->cascadeOnDelete();
            $table->dateTime('check_time');
            $table->decimal('weight_1', 10, 3)->nullable();
            $table->decimal('weight_2', 10, 3)->nullable();
            $table->decimal('weight_3', 10, 3)->nullable();
            $table->decimal('weight_4', 10, 3)->nullable();
            $table->decimal('weight_5', 10, 3)->nullable();
            $table->decimal('weight_6', 10, 3)->nullable();
            $table->decimal('average_weight', 10, 3)->nullable();
            $table->string('result')->default('pass'); // pass | fail
            $table->foreignId('signed_by')->constrained('users'); // NO ACTION
            $table->dateTime('signed_at');
            $table->timestamps();

            $table->index('packing_run_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_weight_checks');
    }
};
