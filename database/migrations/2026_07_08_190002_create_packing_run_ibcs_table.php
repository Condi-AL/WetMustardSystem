<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Packing Run IBC consumption (scope entity: PackingRunIBC).
 * Records which pallecon/IBC (bulk source) was consumed by a packing run.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_run_ibcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_run_id')->constrained('packing_runs')->cascadeOnDelete();
            $table->foreignId('pallecon_record_id')->nullable()->constrained('pallecon_records'); // NO ACTION
            $table->string('source_batch_number')->nullable();
            $table->string('source_mo_number')->nullable();
            $table->dateTime('time_on')->nullable();
            $table->dateTime('time_off')->nullable();
            $table->timestamps();

            $table->index('packing_run_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_run_ibcs');
    }
};
