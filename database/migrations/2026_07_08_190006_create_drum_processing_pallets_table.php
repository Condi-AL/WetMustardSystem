<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Drum Processing Pallet (scope entity: DrumProcessingPallet).
 * A pallecon-to-drum pallet within a drum processing run.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drum_processing_pallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drum_processing_run_id')->constrained('drum_processing_runs')->cascadeOnDelete();
            $table->string('pallecon_number')->nullable();
            $table->string('pallet_ticket_number')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('finish_time')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users'); // NO ACTION
            $table->timestamps();

            $table->index('drum_processing_run_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drum_processing_pallets');
    }
};
