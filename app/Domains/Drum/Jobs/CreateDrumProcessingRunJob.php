<?php

namespace App\Domains\Drum\Jobs;

use App\Models\BatchRecord;
use App\Models\DrumProcessingRun;
use App\Models\User;

/**
 * Creates a drum processing run against a batch (Route B).
 */
class CreateDrumProcessingRunJob
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(BatchRecord $batch, array $attributes = [], ?User $user = null): DrumProcessingRun
    {
        return DrumProcessingRun::create([
            'batch_record_id' => $batch->id,
            'product_id' => $batch->product_id,
            'mo_number' => $batch->manufacturingOrder?->mo_number,
            'shift' => $attributes['shift'] ?? null,
            'operator' => $attributes['operator'] ?? $user?->name,
            'bbe_matches_winman' => $attributes['bbe_matches_winman'] ?? null,
            'status' => DrumProcessingRun::STATUS_OPEN,
        ]);
    }
}
