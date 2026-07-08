<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DBMTS Batch Process Step (scope entity: BatchProcessStep).
 */
class BatchProcessStep extends Model
{
    protected $fillable = [
        'batch_record_id',
        'step_name',
        'sequence',
        'required_flag',
        'completed_by',
        'completed_at',
        'comments',
    ];

    protected function casts(): array
    {
        return [
            'required_flag' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function batchRecord(): BelongsTo
    {
        return $this->belongsTo(BatchRecord::class);
    }

    public function parameters(): HasMany
    {
        return $this->hasMany(BatchProcessParameter::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
