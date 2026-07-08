<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DBMTS Batch Process Parameter (scope entity: BatchProcessParameter).
 */
class BatchProcessParameter extends Model
{
    protected $fillable = [
        'batch_record_id',
        'batch_process_step_id',
        'parameter_name',
        'value',
        'uom',
        'entered_by',
        'entered_at',
    ];

    protected function casts(): array
    {
        return [
            'entered_at' => 'datetime',
        ];
    }

    public function batchRecord(): BelongsTo
    {
        return $this->belongsTo(BatchRecord::class);
    }

    public function processStep(): BelongsTo
    {
        return $this->belongsTo(BatchProcessStep::class, 'batch_process_step_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
