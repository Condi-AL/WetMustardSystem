<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DBMTS Drum Processing Run (scope entity: DrumProcessingRun).
 */
class DrumProcessingRun extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'batch_record_id', 'product_id', 'mo_number', 'shift', 'operator', 'bbe_matches_winman', 'status',
    ];

    protected function casts(): array
    {
        return ['bbe_matches_winman' => 'boolean'];
    }

    public function batchRecord(): BelongsTo
    {
        return $this->belongsTo(BatchRecord::class);
    }

    public function pallets(): HasMany
    {
        return $this->hasMany(DrumProcessingPallet::class);
    }

    public function palletRecords(): HasMany
    {
        return $this->hasMany(PalletRecord::class);
    }
}
