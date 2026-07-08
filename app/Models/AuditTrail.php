<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DBMTS Audit Trail (scope entity: AuditTrail).
 */
class AuditTrail extends Model
{
    protected $fillable = [
        'entity_name',
        'entity_id',
        'field_name',
        'old_value',
        'new_value',
        'action',
        'reason',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
