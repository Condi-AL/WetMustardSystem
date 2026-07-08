<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Master Data: Recipe Ingredient definition.
 *
 * Maps to scope entity: RecipeIngredient(RecipeIngredientId, RecipeId, VariantId,
 * MaterialCode, MaterialDescription, Percentage, RequiredQuantity, UOM, Sequence).
 *
 * variant_id is nullable: an ingredient may be defined at recipe level (shared by
 * all variants) or overridden per batch-size variant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('recipe_variants')->nullOnDelete();
            $table->string('material_code');
            $table->string('material_description')->nullable();
            $table->decimal('percentage', 8, 4)->nullable();
            $table->decimal('required_quantity', 12, 3)->nullable();
            $table->string('uom')->nullable();
            $table->integer('sequence')->nullable();
            $table->timestamps();

            $table->index(['recipe_id', 'variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};
