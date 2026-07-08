<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS local record of a selected WinMan manufacturing order.
 *
 * Maps to scope entity: ManufacturingOrder(MOId, MONumber, WinManManufacturingOrder,
 * WinManManufacturingOrderId, WinManProductInternal, WinManProductId, RecipeCode,
 * VariantId, ProductId, PlannedQuantity, QuantityOutstanding, WinManSystemType,
 * WinManLastModifiedDate, Status, CreatedDate).
 *
 * DBMTS consumes existing WinMan MOs only; it never creates WinMan MOs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturing_orders', function (Blueprint $table) {
            $table->id();
            $table->string('mo_number');                        // human-readable MO reference
            $table->bigInteger('winman_manufacturing_order');   // internal BIGINT (SP context)
            $table->string('winman_manufacturing_order_id');    // WinMan ManufacturingOrderId
            $table->string('winman_product_internal')->nullable();
            $table->string('winman_product_id')->nullable();    // WinMan Products.ProductId (string)

            $table->string('recipe_code')->nullable();
            $table->foreignId('variant_id')->nullable()->constrained('recipe_variants')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            $table->decimal('planned_quantity', 14, 3)->default(0);
            $table->decimal('quantity_outstanding', 14, 3)->default(0);
            $table->char('winman_system_type', 1)->nullable();
            $table->dateTime('winman_last_modified_date')->nullable(); // concurrency snapshot at selection

            $table->string('status')->default('selected');
            $table->foreignId('selected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('winman_manufacturing_order');
            $table->index('winman_product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing_orders');
    }
};
