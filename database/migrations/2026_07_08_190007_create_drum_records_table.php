<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Drum Record (scope entity: DrumRecord).
 * An individual drum within a drum processing pallet: number, fill weight,
 * bag seal, drum seal and liner check.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drum_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drum_processing_pallet_id')->constrained('drum_processing_pallets')->cascadeOnDelete();
            $table->string('drum_number');
            $table->dateTime('drum_time')->nullable();
            $table->decimal('filler_weight', 10, 3)->nullable();
            $table->string('bag_seal_number')->nullable();
            $table->string('drum_seal_number')->nullable();
            $table->boolean('liner_clean_undamaged')->nullable();
            $table->timestamps();

            $table->index('drum_processing_pallet_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drum_records');
    }
};
