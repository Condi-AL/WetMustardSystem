<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DBMTS Bucket Packing Run (scope entity: PackingRun).
 */
class PackingRun extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'batch_record_id', 'product_id', 'mo_number', 'packing_date', 'shift', 'status', 'created_by',
    ];

    protected function casts(): array
    {
        return ['packing_date' => 'date'];
    }

    public function batchRecord(): BelongsTo
    {
        return $this->belongsTo(BatchRecord::class);
    }

    public function ibcs(): HasMany
    {
        return $this->hasMany(PackingRunIbc::class);
    }

    public function hourlyChecks(): HasMany
    {
        return $this->hasMany(PackingHourlyCheck::class);
    }

    public function weightChecks(): HasMany
    {
        return $this->hasMany(PackingWeightCheck::class);
    }

    public function pallets(): HasMany
    {
        return $this->hasMany(PalletRecord::class);
    }
}
