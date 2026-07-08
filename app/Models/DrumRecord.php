<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DBMTS Drum Record (scope entity: DrumRecord).
 */
class DrumRecord extends Model
{
    protected $fillable = [
        'drum_processing_pallet_id', 'drum_number', 'drum_time', 'filler_weight',
        'bag_seal_number', 'drum_seal_number', 'liner_clean_undamaged',
    ];

    protected function casts(): array
    {
        return [
            'drum_time' => 'datetime',
            'filler_weight' => 'decimal:3',
            'liner_clean_undamaged' => 'boolean',
        ];
    }

    public function pallet(): BelongsTo
    {
        return $this->belongsTo(DrumProcessingPallet::class, 'drum_processing_pallet_id');
    }
}
