<?php

namespace App\Domains\Drum\Jobs;

use App\Models\DrumProcessingPallet;
use App\Models\DrumRecord;

/**
 * Adds an individual drum record to a drum processing pallet: drum number, fill
 * weight, bag seal, drum seal and liner check (scope acceptance criterion 12).
 */
class AddDrumRecordJob
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(DrumProcessingPallet $pallet, array $attributes): DrumRecord
    {
        return $pallet->drumRecords()->create([
            'drum_number' => $attributes['drum_number'],
            'drum_time' => $attributes['drum_time'] ?? now(),
            'filler_weight' => $attributes['filler_weight'] ?? null,
            'bag_seal_number' => $attributes['bag_seal_number'] ?? null,
            'drum_seal_number' => $attributes['drum_seal_number'] ?? null,
            'liner_clean_undamaged' => $attributes['liner_clean_undamaged'] ?? null,
        ]);
    }
}
