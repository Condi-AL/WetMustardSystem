<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Notification Rule (scope entity: NotificationRule).
 * Configurable real-time alert rules (scope §14.3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_key')->unique();
            $table->string('rule_name');
            $table->string('event_type');                 // event | detector
            $table->string('trigger_condition')->nullable(); // e.g. threshold hours for detectors
            $table->string('severity')->default('warning'); // info | warning | critical
            $table->boolean('enabled')->default(true);
            $table->integer('cooldown_minutes')->default(60);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_rules');
    }
};
