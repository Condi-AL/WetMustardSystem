<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DBMTS Notification Rule (scope entity: NotificationRule).
 */
class NotificationRule extends Model
{
    public const SEVERITY_INFO = 'info';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_CRITICAL = 'critical';

    protected $fillable = [
        'rule_key', 'rule_name', 'event_type', 'trigger_condition', 'severity', 'enabled', 'cooldown_minutes',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'cooldown_minutes' => 'integer',
        ];
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(NotificationRecipient::class, 'rule_key', 'rule_key');
    }

    public function events(): HasMany
    {
        return $this->hasMany(NotificationEvent::class, 'rule_key', 'rule_key');
    }
}
