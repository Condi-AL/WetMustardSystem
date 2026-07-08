<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Manufacturing Batch Record header (scope entity: BatchRecord).
 *
 * The electronic batchcard for a production run against a selected WinMan MO.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacturing_order_id')->constrained('manufacturing_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('recipe_variants')->nullOnDelete();

            $table->string('batch_number')->unique();
            $table->date('production_date');
            $table->string('shift')->nullable();
            $table->decimal('planned_quantity', 14, 3)->nullable();

            $table->string('status')->default('in_progress'); // in_progress | completed | qa_review | closed

            // User FKs use NO ACTION (SQL Server disallows multiple cascade/set-null
            // paths to the same table); completed records are never hard-deleted.
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('completed_by')->nullable()->constrained('users');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'production_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_records');
    }
};
