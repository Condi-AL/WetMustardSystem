<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DBMTS Electronic Signature (scope entity: ElectronicSignature).
 */
class ElectronicSignature extends Model
{
    protected $fillable = [
        'entity_name',
        'entity_id',
        'signature_purpose',
        'user_id',
        'signed_at',
        'meaning',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
