<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DBMTS Pallet Record (scope entity: PalletRecord).
 */
class PalletRecord extends Model
{
    protected $fillable = [
        'packing_run_id', 'drum_processing_run_id', 'pallet_number', 'time',
        'ticket_number', 'pallet_amount', 'bbe_pallet_label',
    ];

    protected function casts(): array
    {
        return ['time' => 'datetime', 'pallet_amount' => 'integer'];
    }

    public function packingRun(): BelongsTo
    {
        return $this->belongsTo(PackingRun::class);
    }

    public function drumProcessingRun(): BelongsTo
    {
        return $this->belongsTo(DrumProcessingRun::class);
    }
}
