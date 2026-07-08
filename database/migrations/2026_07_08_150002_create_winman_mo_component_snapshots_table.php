<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS local snapshot of the WinMan live BOM / components at the point a
 * manufacturing order is selected or refreshed.
 *
 * Maps to scope entity: WinManMOComponentSnapshot(ComponentSnapshotId, MOId,
 * WinManManufacturingOrder, WinManComponentProduct, WinManComponentProductId,
 * ComponentDescription, Classification, QuantityIssued, QuantityOutstanding,
 * SnapshotAt). Stored so the electronic record shows which WinMan components
 * were expected at the point of production.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winman_mo_component_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacturing_order_id')->constrained('manufacturing_orders')->cascadeOnDelete();
            $table->bigInteger('winman_manufacturing_order');
            $table->char('item_type', 1)->nullable();
            $table->string('winman_component_product')->nullable();     // internal component product key
            $table->string('winman_component_product_id')->nullable();  // component ProductId (string)
            $table->string('component_description')->nullable();
            $table->string('classification')->nullable();
            $table->decimal('quantity', 16, 5)->default(0);
            $table->decimal('quantity_issued', 16, 5)->default(0);
            $table->decimal('quantity_outstanding', 16, 5)->default(0);
            $table->dateTime('snapshot_at');
            $table->timestamps();

            $table->index(['manufacturing_order_id', 'snapshot_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winman_mo_component_snapshots');
    }
};
