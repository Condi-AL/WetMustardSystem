<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WinManIssueLog extends Model
{
    protected $table = 'winman_issue_logs';

    public const STATUS_SUCCESS = 'success';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'batch_record_id',
        'batch_ingredient_lot_id',
        'component_snapshot_id',
        'winman_work_in_progress',
        'winman_manufacturing_order',
        'material_code',
        'lot_number',
        'quantity_issued',
        'winman_inventory_ids',
        'issue_user',
        'issue_date',
        'issue_status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'quantity_issued' => 'decimal:3',
            'issue_date' => 'datetime',
            'winman_inventory_ids' => 'array',
            'winman_work_in_progress' => 'integer',
            'winman_manufacturing_order' => 'integer',
        ];
    }

    public function batchRecord(): BelongsTo
    {
        return $this->belongsTo(BatchRecord::class);
    }

    public function batchIngredientLot(): BelongsTo
    {
        return $this->belongsTo(BatchIngredientLot::class);
    }

    public function componentSnapshot(): BelongsTo
    {
        return $this->belongsTo(WinManMoComponentSnapshot::class, 'component_snapshot_id');
    }
}
