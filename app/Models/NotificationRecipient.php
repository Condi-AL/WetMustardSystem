<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * DBMTS Notification Recipient (scope entity: NotificationRecipient).
 */
class NotificationRecipient extends Model
{
    public const TYPE_DIRECT = 'direct';
    public const TYPE_ROLE = 'role';

    protected $fillable = [
        'rule_key', 'recipient_type', 'recipient_email', 'recipient_name', 'role_key', 'enabled',
    ];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}
