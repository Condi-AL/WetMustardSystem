<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Master Data: Recipe Header.
 *
 * Maps to scope entity: RecipeHeader(RecipeId, RecipeCode, Revision, IssueDate,
 * PLCRecipeNumber, SourceDocumentCode).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('recipe_code')->index();
            $table->string('revision')->nullable();
            $table->date('issue_date')->nullable();
            $table->string('plc_recipe_number')->nullable();
            $table->string('source_document_code')->nullable();   // -> document_references.code
            $table->boolean('active_flag')->default(true);
            $table->timestamps();

            $table->unique(['recipe_code', 'revision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
