<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DBMTS Pallecon Filling record (scope entity: PalleconRecord).
 */
class PalleconRecord extends Model
{
    protected $fillable = [
        'batch_record_id',
        'mo_number',
        'ticket_number',
        'serial_number',
        'top_seal_number',
        'bottom_seal_number',
        'liner_number',
        'liner_batch_code',
        'fill_weight',
        'start_time',
        'finish_time',
        'checked_by',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'fill_weight' => 'decimal:3',
            'start_time' => 'datetime',
            'finish_time' => 'datetime',
            'checked_at' => 'datetime',
        ];
    }

    public function batchRecord(): BelongsTo
    {
        return $this->belongsTo(BatchRecord::class);
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function submissionAudits(): HasMany
    {
        return $this->hasMany(PalleconSubmissionAudit::class);
    }
}
