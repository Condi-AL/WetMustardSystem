<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Pallecon Filling record (scope entity: PalleconRecord, replaces WM004).
 *
 * Links bulk product to pallecon/IBC traceability: serials, seals, liner checks
 * and fill weights. A batch may be linked to one or more pallecons.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pallecon_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_record_id')->constrained('batch_records')->cascadeOnDelete();
            $table->string('mo_number')->nullable();
            $table->string('ticket_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('top_seal_number')->nullable();
            $table->string('bottom_seal_number')->nullable();
            $table->string('liner_number')->nullable();
            $table->string('liner_batch_code')->nullable();
            $table->decimal('fill_weight', 12, 3)->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('finish_time')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users');
            $table->dateTime('checked_at')->nullable();
            $table->timestamps();

            $table->index('batch_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pallecon_records');
    }
};
