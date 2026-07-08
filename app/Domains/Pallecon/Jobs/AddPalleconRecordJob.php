<?php

namespace App\Domains\Pallecon\Jobs;

use App\Models\BatchRecord;
use App\Models\PalleconRecord;
use App\Models\User;

/**
 * Adds a pallecon filling record to a batch (serials, seals, liner checks and
 * fill weight). A batch may have one or more pallecons.
 */
class AddPalleconRecordJob
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(BatchRecord $batch, array $attributes, ?User $user = null): PalleconRecord
    {
        return $batch->pallecons()->create([
            'mo_number' => $attributes['mo_number'] ?? $batch->manufacturingOrder?->mo_number,
            'ticket_number' => $attributes['ticket_number'] ?? null,
            'serial_number' => $attributes['serial_number'] ?? null,
            'top_seal_number' => $attributes['top_seal_number'] ?? null,
            'bottom_seal_number' => $attributes['bottom_seal_number'] ?? null,
            'liner_number' => $attributes['liner_number'] ?? null,
            'liner_batch_code' => $attributes['liner_batch_code'] ?? null,
            'fill_weight' => $attributes['fill_weight'] ?? null,
            'start_time' => $attributes['start_time'] ?? null,
            'finish_time' => $attributes['finish_time'] ?? null,
            'checked_by' => $user?->id,
            'checked_at' => $user !== null ? now() : null,
        ]);
    }
}
