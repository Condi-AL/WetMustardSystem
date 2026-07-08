<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('winman_mo_component_snapshots', function (Blueprint $table) {
            $table->unsignedBigInteger('winman_work_in_progress')->nullable()->after('winman_manufacturing_order');
            $table->index(['manufacturing_order_id', 'winman_work_in_progress'], 'wm_mo_snapshot_mo_wip_idx');
        });
    }

    public function down(): void
    {
        Schema::table('winman_mo_component_snapshots', function (Blueprint $table) {
            $table->dropIndex('wm_mo_snapshot_mo_wip_idx');
            $table->dropColumn('winman_work_in_progress');
        });
    }
};
