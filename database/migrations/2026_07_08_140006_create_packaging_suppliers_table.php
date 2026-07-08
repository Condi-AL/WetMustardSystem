<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Master Data: Packaging Supplier Master.
 *
 * Maps to scope: Supplier names, reference type (lot/job or NVE number) and
 * packaging type. Supports both traditional lot/job supplier formats and
 * NVE-number supplier formats (e.g. Jokey).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packaging_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_name');
            $table->string('reference_type');       // 'lot_job' | 'nve'
            $table->string('packaging_type')->nullable(); // e.g. Bucket, Lid, Drum
            $table->boolean('active_flag')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packaging_suppliers');
    }
};
