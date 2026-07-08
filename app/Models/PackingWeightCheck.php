<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DBMTS Packing Weight Check (scope entity: PackingWeightCheck).
 */
class PackingWeightCheck extends Model
{
    public const RESULT_PASS = 'pass';
    public const RESULT_FAIL = 'fail';

    protected $fillable = [
        'packing_run_id', 'check_time', 'weight_1', 'weight_2', 'weight_3', 'weight_4', 'weight_5', 'weight_6',
        'average_weight', 'result', 'signed_by', 'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'check_time' => 'datetime',
            'signed_at' => 'datetime',
            'weight_1' => 'decimal:3', 'weight_2' => 'decimal:3', 'weight_3' => 'decimal:3',
            'weight_4' => 'decimal:3', 'weight_5' => 'decimal:3', 'weight_6' => 'decimal:3',
            'average_weight' => 'decimal:3',
        ];
    }

    public function packingRun(): BelongsTo
    {
        return $this->belongsTo(PackingRun::class);
    }

    public function signedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }
}
