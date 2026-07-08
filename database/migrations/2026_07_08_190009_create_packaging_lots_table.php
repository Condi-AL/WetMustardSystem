<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Packaging Lot traceability (scope entity: PackagingLot, replaces
 * WM014/WM015/WM047). Supports both traditional lot/job supplier formats and
 * NVE-number supplier formats. Link FKs are NO ACTION.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packaging_lots', function (Blueprint $table) {
            $table->id();
            $table->string('packaging_type');            // Bucket | Lid | Drum | ...
            $table->string('supplier')->nullable();
            $table->string('supplier_reference_type');   // lot_job | nve
            $table->string('supplier_reference_number')->nullable();
            $table->date('supplier_production_date')->nullable();
            $table->string('machine_number')->nullable();
            $table->string('lot_or_job_number')->nullable();
            $table->dateTime('time_on')->nullable();
            $table->string('operator_name')->nullable();

            $table->string('linked_mo')->nullable();
            $table->foreignId('batch_record_id')->nullable()->constrained('batch_records');
            $table->foreignId('linked_packing_run_id')->nullable()->constrained('packing_runs');
            $table->foreignId('linked_drum_run_id')->nullable()->constrained('drum_processing_runs');
            $table->timestamps();

            $table->index('batch_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packaging_lots');
    }
};
