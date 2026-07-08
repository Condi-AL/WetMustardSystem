<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DBMTS Report Configuration (scope entity: ReportConfig).
 */
class ReportConfig extends Model
{
    protected $fillable = [
        'report_key', 'report_name', 'report_type', 'schedule_time',
        'date_offset_from_days', 'date_offset_to_days', 'enabled', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date_offset_from_days' => 'integer',
            'date_offset_to_days' => 'integer',
            'enabled' => 'boolean',
        ];
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(ReportRecipient::class, 'report_key', 'report_key');
    }

    public function sendLogs(): HasMany
    {
        return $this->hasMany(ReportSendLog::class, 'report_key', 'report_key');
    }
}
