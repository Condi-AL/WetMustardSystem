<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('winman_issue_logs', function (Blueprint $table) {
            $table->dropForeign(['component_snapshot_id']);

            $table->foreign('component_snapshot_id')
                ->references('id')
                ->on('winman_mo_component_snapshots')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('winman_issue_logs', function (Blueprint $table) {
            $table->dropForeign(['component_snapshot_id']);

            $table->foreign('component_snapshot_id')
                ->references('id')
                ->on('winman_mo_component_snapshots');
        });
    }
};
