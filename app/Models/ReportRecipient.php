<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * DBMTS Report Recipient (scope entity: ReportRecipient).
 */
class ReportRecipient extends Model
{
    public const TYPE_DIRECT = 'direct';
    public const TYPE_ROLE = 'role';

    protected $fillable = [
        'report_key', 'recipient_type', 'recipient_email', 'recipient_name', 'role_key', 'is_cc', 'enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_cc' => 'boolean',
            'enabled' => 'boolean',
        ];
    }
}
