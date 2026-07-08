<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Notification Event (scope entity: NotificationEvent).
 * A raised alert instance with acknowledge/resolve lifecycle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_events', function (Blueprint $table) {
            $table->id();
            $table->string('rule_key');
            $table->string('entity_name')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('severity')->default('warning');
            $table->string('message');
            $table->string('status')->default('open'); // open | acknowledged | resolved
            $table->dateTime('triggered_at');
            $table->foreignId('acknowledged_by')->nullable()->constrained('users'); // NO ACTION
            $table->dateTime('acknowledged_at')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['rule_key', 'entity_name', 'entity_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_events');
    }
};
