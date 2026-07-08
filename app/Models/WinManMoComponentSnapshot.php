<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DBMTS local snapshot of a WinMan live BOM component (scope entity:
 * WinManMOComponentSnapshot).
 */
class WinManMoComponentSnapshot extends Model
{
    protected $table = 'winman_mo_component_snapshots';

    protected $fillable = [
        'manufacturing_order_id',
        'winman_manufacturing_order',
        'winman_work_in_progress',
        'item_type',
        'winman_component_product',
        'winman_component_product_id',
        'component_description',
        'classification',
        'quantity',
        'quantity_issued',
        'quantity_outstanding',
        'snapshot_at',
    ];

    protected function casts(): array
    {
        return [
            'winman_manufacturing_order' => 'integer',
            'winman_work_in_progress' => 'integer',
            'quantity' => 'decimal:5',
            'quantity_issued' => 'decimal:5',
            'quantity_outstanding' => 'decimal:5',
            'snapshot_at' => 'datetime',
        ];
    }

    public function manufacturingOrder(): BelongsTo
    {
        return $this->belongsTo(ManufacturingOrder::class);
    }
}
