<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DBMTS Metal Detector Verification (scope entity: MetalDetectorCheck).
 */
class MetalDetectorCheck extends Model
{
    public const TYPE_START = 'start_of_shift';
    public const TYPE_HOURLY = 'hourly';
    public const TYPE_END = 'end_of_shift';

    public const RESULT_PASS = 'pass';
    public const RESULT_FAIL = 'fail';

    protected $fillable = [
        'batch_record_id',
        'manufacturing_order_id',
        'product_id',
        'check_type',
        'check_time',
        'fe10_pass',
        'non_fe15_pass',
        'ss20_pass',
        'overall_result',
        'bin_locked',
        'bin_empty',
        'is_recheck',
        'failure_action',
        'comments',
        'signed_by',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'check_time' => 'datetime',
            'signed_at' => 'datetime',
            'fe10_pass' => 'boolean',
            'non_fe15_pass' => 'boolean',
            'ss20_pass' => 'boolean',
            'bin_locked' => 'boolean',
            'bin_empty' => 'boolean',
            'is_recheck' => 'boolean',
        ];
    }

    public function batchRecord(): BelongsTo
    {
        return $this->belongsTo(BatchRecord::class);
    }

    public function signedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }
}
