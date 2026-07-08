<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Report Configuration Master (scope entity: ReportConfig).
 * Configurable scheduled reports with schedule time and date offsets.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_configs', function (Blueprint $table) {
            $table->id();
            $table->string('report_key')->unique();
            $table->string('report_name');
            $table->string('report_type')->default('scheduled'); // scheduled | on_demand
            $table->string('schedule_time')->nullable();          // e.g. 06:00
            $table->integer('date_offset_from_days')->default(-1);
            $table->integer('date_offset_to_days')->default(-1);
            $table->boolean('enabled')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users'); // NO ACTION
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_configs');
    }
};
