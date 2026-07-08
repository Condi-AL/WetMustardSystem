<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Metal Detector Verification (scope entity: MetalDetectorCheck, replaces
 * WM011). CCP checks at start of shift, hourly and end of shift with FE, non-FE
 * and SS test-piece results and failure handling.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metal_detector_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_record_id')->nullable()->constrained('batch_records')->nullOnDelete();
            // NO ACTION avoids multiple cascade paths to this table via manufacturing_orders/products.
            $table->foreignId('manufacturing_order_id')->nullable()->constrained('manufacturing_orders');
            $table->foreignId('product_id')->nullable()->constrained('products');

            $table->string('check_type');          // start_of_shift | hourly | end_of_shift
            $table->dateTime('check_time');

            $table->boolean('fe10_pass');           // Fe 1.0mm test piece
            $table->boolean('non_fe15_pass');       // Non-Fe 1.5mm test piece
            $table->boolean('ss20_pass');           // Stainless 2.0mm test piece
            $table->string('overall_result');       // pass | fail (derived)

            $table->boolean('bin_locked')->nullable();
            $table->boolean('bin_empty')->nullable();
            $table->boolean('is_recheck')->default(false);
            $table->string('failure_action', 500)->nullable(); // escalation notes on failure
            $table->string('comments', 500)->nullable();

            $table->foreignId('signed_by')->constrained('users');
            $table->dateTime('signed_at');
            $table->timestamps();

            $table->index(['batch_record_id', 'check_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metal_detector_checks');
    }
};
