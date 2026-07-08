<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DBMTS Drum Processing Pallet (scope entity: DrumProcessingPallet).
 */
class DrumProcessingPallet extends Model
{
    protected $fillable = [
        'drum_processing_run_id', 'pallecon_number', 'pallet_ticket_number', 'start_time', 'finish_time', 'checked_by',
    ];

    protected function casts(): array
    {
        return ['start_time' => 'datetime', 'finish_time' => 'datetime'];
    }

    public function drumProcessingRun(): BelongsTo
    {
        return $this->belongsTo(DrumProcessingRun::class);
    }

    public function drumRecords(): HasMany
    {
        return $this->hasMany(DrumRecord::class);
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
