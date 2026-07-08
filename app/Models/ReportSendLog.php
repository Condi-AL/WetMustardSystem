<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DBMTS Report Send Log (scope entity: ReportSendLog).
 */
class ReportSendLog extends Model
{
    public const STATUS_RUNNING = 'running';
    public const STATUS_SENT = 'sent';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'report_key', 'trigger_mode', 'date_from', 'date_to', 'recipients_to', 'recipients_cc',
        'status', 'error_message', 'row_count', 'triggered_by', 'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'date_from' => 'date',
            'date_to' => 'date',
            'row_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
