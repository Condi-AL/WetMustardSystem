<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winman_issue_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_record_id')->nullable()->constrained('batch_records');
            $table->foreignId('batch_ingredient_lot_id')->nullable()->constrained('batch_ingredient_lots');
            $table->foreignId('component_snapshot_id')->nullable()->constrained('winman_mo_component_snapshots')->nullOnDelete();
            $table->unsignedBigInteger('winman_work_in_progress')->nullable();
            $table->unsignedBigInteger('winman_manufacturing_order')->nullable();
            $table->string('material_code')->nullable();
            $table->string('lot_number')->nullable();
            $table->decimal('quantity_issued', 14, 3)->nullable();
            $table->json('winman_inventory_ids')->nullable();
            $table->string('issue_user')->nullable();
            $table->dateTime('issue_date');
            $table->string('issue_status'); // success | rejected | failed
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['batch_record_id', 'issue_status']);
            $table->index('batch_ingredient_lot_id');
            $table->index('winman_work_in_progress');
            $table->index('winman_manufacturing_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winman_issue_logs');
    }
};
