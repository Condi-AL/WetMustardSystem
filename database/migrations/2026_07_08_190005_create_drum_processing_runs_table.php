<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Drum Processing Run (scope entity: DrumProcessingRun, replaces WM046).
 * Route B drum processing against a batch/MO.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drum_processing_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_record_id')->constrained('batch_records')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products'); // NO ACTION
            $table->string('mo_number')->nullable();
            $table->string('shift')->nullable();
            $table->string('operator')->nullable();
            $table->boolean('bbe_matches_winman')->nullable();
            $table->string('status')->default('open'); // open | closed
            $table->timestamps();

            $table->index('batch_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drum_processing_runs');
    }
};
