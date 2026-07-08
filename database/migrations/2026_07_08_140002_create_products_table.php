<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Master Data: Product Master.
 *
 * Maps to scope entity: ProductMaster(ProductId, RecipeCode, ProductName,
 * FinishedGoodsCode, IntermediateCode, WinManProductId, WinManProductInternal,
 * WinManPackSize, ShelfLife, PackFormat, PalletType, AmountPerPallet, Customer,
 * ActiveFlag).
 *
 * WinMan mapping path: recipe_code -> finished_goods_code -> winman_product_id.
 * winman_product_id MUST be treated as a string, not an integer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('recipe_code')->index();
            $table->string('product_name');
            $table->string('finished_goods_code')->nullable()->index();
            $table->string('intermediate_code')->nullable();

            // WinMan integration identifiers (external references, not FKs).
            $table->string('winman_product_id')->nullable()->index();   // WinMan Products.ProductId (string)
            $table->string('winman_product_internal')->nullable();      // WinMan Products.Product (internal key)
            $table->decimal('winman_pack_size', 12, 3)->nullable();

            $table->integer('shelf_life_days')->nullable();
            $table->string('pack_format')->nullable();
            $table->string('pallet_type')->nullable();
            $table->integer('amount_per_pallet')->nullable();
            $table->string('customer')->nullable();
            $table->boolean('active_flag')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
