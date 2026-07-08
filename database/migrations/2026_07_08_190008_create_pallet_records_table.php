<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Pallet Record (scope entity: PalletRecord).
 * A finished pallet produced by either a packing run or a drum processing run.
 * Both run FKs are NO ACTION to avoid SQL Server multiple cascade paths.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pallet_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_run_id')->nullable()->constrained('packing_runs');
            $table->foreignId('drum_processing_run_id')->nullable()->constrained('drum_processing_runs');
            $table->string('pallet_number');
            $table->dateTime('time')->nullable();
            $table->string('ticket_number')->nullable();
            $table->integer('pallet_amount')->nullable();
            $table->string('bbe_pallet_label')->nullable();
            $table->timestamps();

            $table->index(['packing_run_id', 'drum_processing_run_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pallet_records');
    }
};
