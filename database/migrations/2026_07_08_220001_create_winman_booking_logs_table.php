<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS WinMan Booking Log (scope entity: WinManBookingLog). Records every
 * attempted and successful finished-goods booking for duplicate prevention and
 * audit (scope §11.6).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winman_booking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_record_id')->nullable()->constrained('batch_records'); // NO ACTION
            $table->unsignedBigInteger('winman_inventory_id')->nullable();  // returned Inventory ID
            $table->bigInteger('winman_manufacturing_order');
            $table->string('winman_manufacturing_order_id')->nullable();
            $table->string('winman_product_internal')->nullable();
            $table->string('winman_product_id')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('lot_number')->nullable();
            $table->decimal('quantity_booked_kg', 14, 3)->nullable();
            $table->decimal('quantity_booked_traded_units', 14, 3)->nullable();
            $table->string('booking_user')->nullable();
            $table->dateTime('booking_date');
            $table->string('booking_status');           // success | rejected | failed
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['batch_record_id', 'booking_status']);
            $table->index('winman_manufacturing_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winman_booking_logs');
    }
};
