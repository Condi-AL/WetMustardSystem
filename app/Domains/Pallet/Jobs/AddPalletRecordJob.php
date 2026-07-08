<?php

namespace App\Domains\Pallet\Jobs;

use App\Models\PalletRecord;

/**
 * Adds a finished pallet record for either a packing run or a drum processing
 * run (scope entity: PalletRecord).
 */
class AddPalletRecordJob
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes): PalletRecord
    {
        return PalletRecord::create([
            'packing_run_id' => $attributes['packing_run_id'] ?? null,
            'drum_processing_run_id' => $attributes['drum_processing_run_id'] ?? null,
            'pallet_number' => $attributes['pallet_number'],
            'time' => $attributes['time'] ?? now(),
            'ticket_number' => $attributes['ticket_number'] ?? null,
            'pallet_amount' => $attributes['pallet_amount'] ?? null,
            'bbe_pallet_label' => $attributes['bbe_pallet_label'] ?? null,
        ]);
    }
}
