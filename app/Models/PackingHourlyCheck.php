<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DBMTS Packing Hourly Check (scope entity: PackingHourlyCheck).
 */
class PackingHourlyCheck extends Model
{
    protected $fillable = [
        'packing_run_id', 'check_time', 'bucket_clean', 'lid_clean', 'lids_secure', 'tamper_in_place',
        'label_correct', 'print_clear', 'lot_code_correct', 'filler_clean', 'fill_clean', 'signed_by', 'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'check_time' => 'datetime',
            'signed_at' => 'datetime',
            'bucket_clean' => 'boolean',
            'lid_clean' => 'boolean',
            'lids_secure' => 'boolean',
            'tamper_in_place' => 'boolean',
            'label_correct' => 'boolean',
            'print_clear' => 'boolean',
            'lot_code_correct' => 'boolean',
            'filler_clean' => 'boolean',
            'fill_clean' => 'boolean',
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
