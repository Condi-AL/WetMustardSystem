<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Bucket Packing Run (scope entity: PackingRun, replaces WM016).
 * Route A finished-goods packing against a batch/MO.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_record_id')->constrained('batch_records')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products'); // NO ACTION
            $table->string('mo_number')->nullable();
            $table->date('packing_date');
            $table->string('shift')->nullable();
            $table->string('status')->default('open'); // open | closed
            $table->foreignId('created_by')->nullable()->constrained('users'); // NO ACTION
            $table->timestamps();

            $table->index('batch_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_runs');
    }
};
