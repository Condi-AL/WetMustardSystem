<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Notification Recipient (scope entity: NotificationRecipient).
 * rule_key NULL = applies to all rules. recipient_type is 'direct' or 'role'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('rule_key')->nullable();      // NULL = all rules
            $table->string('recipient_type')->default('direct'); // direct | role
            $table->string('recipient_email')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('role_key')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->index('rule_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_recipients');
    }
};
