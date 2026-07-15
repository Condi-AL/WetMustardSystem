<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stores the combined WinMan booking preview and label preview payload captured
 * when a pallecon submission is completed.
 */
class PalleconSubmissionAudit extends Model
{
    protected $fillable = [
        'batch_record_id',
        'pallecon_record_id',
        'submitted_by',
        'submitted_at',
        'booking_status',
        'print_status',
        'winman_preview',
        'label_preview',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'winman_preview' => 'array',
            'label_preview' => 'array',
        ];
    }

    public function batchRecord(): BelongsTo
    {
        return $this->belongsTo(BatchRecord::class);
    }

    public function palleconRecord(): BelongsTo
    {
        return $this->belongsTo(PalleconRecord::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
