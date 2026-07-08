<?php

namespace App\Domains\Drum\Jobs;

use App\Models\DrumProcessingPallet;
use App\Models\DrumProcessingRun;
use App\Models\User;

/**
 * Adds a pallet (pallecon-to-drum) to a drum processing run.
 */
class AddDrumProcessingPalletJob
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(DrumProcessingRun $run, array $attributes, ?User $user = null): DrumProcessingPallet
    {
        return $run->pallets()->create([
            'pallecon_number' => $attributes['pallecon_number'] ?? null,
            'pallet_ticket_number' => $attributes['pallet_ticket_number'] ?? null,
            'start_time' => $attributes['start_time'] ?? null,
            'finish_time' => $attributes['finish_time'] ?? null,
            'checked_by' => $user?->id,
        ]);
    }
}
