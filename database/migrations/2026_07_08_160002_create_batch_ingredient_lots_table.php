<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Batch Ingredient Lot (scope entity: BatchIngredientLot).
 *
 * Captures each ingredient lot used in a batch including quantity, weighed-by /
 * weighed-at and tipped-by / tipped-at sign-offs. A lot change creates a new
 * row rather than overwriting existing data (scope §11 validation).
 *
 * material_code/description/required_quantity are denormalised so ingredient
 * capture works before the Recipe Ingredient master is fully populated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_ingredient_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_record_id')->constrained('batch_records')->cascadeOnDelete();
            $table->foreignId('recipe_ingredient_id')->nullable()->constrained('recipe_ingredients')->nullOnDelete();

            $table->string('material_code')->nullable();
            $table->string('material_description')->nullable();
            $table->decimal('required_quantity', 12, 3)->nullable();
            $table->string('uom')->nullable();
            $table->integer('sequence')->nullable();

            $table->string('lot_number')->nullable();
            $table->decimal('actual_quantity', 12, 3)->nullable();

            $table->foreignId('weighed_by')->nullable()->constrained('users');
            $table->dateTime('weighed_at')->nullable();
            $table->foreignId('tipped_by')->nullable()->constrained('users');
            $table->dateTime('tipped_at')->nullable();

            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->index('batch_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_ingredient_lots');
    }
};
