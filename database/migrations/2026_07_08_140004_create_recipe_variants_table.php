<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Master Data: Recipe Batch Variant.
 *
 * Maps to scope entity: RecipeBatchVariant(VariantId, RecipeCode, VariantName,
 * BatchSize, PLCRecipeNumber, SourceDocumentCode, ActiveFlag).
 *
 * A recipe may have multiple approved batch-size variants (e.g. 500kg / 800kg /
 * 400kg half-batch). Recipe and batch size are separate concerns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->nullable()->constrained('recipes')->nullOnDelete();
            $table->string('recipe_code')->index();
            $table->string('variant_name');
            $table->decimal('batch_size', 12, 3);
            $table->string('batch_size_uom')->default('kg');
            $table->string('plc_recipe_number')->nullable();
            $table->string('source_document_code')->nullable();   // -> document_references.code
            $table->boolean('active_flag')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_variants');
    }
};
