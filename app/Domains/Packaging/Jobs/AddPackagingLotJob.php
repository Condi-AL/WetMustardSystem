<?php

namespace App\Domains\Packaging\Jobs;

use App\Models\PackagingLot;

/**
 * Adds a packaging lot traceability record. Supports both traditional lot/job
 * supplier formats and NVE-number supplier formats (scope acceptance criterion
 * 13).
 */
class AddPackagingLotJob
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes): PackagingLot
    {
        return PackagingLot::create([
            'packaging_type' => $attributes['packaging_type'],
            'supplier' => $attributes['supplier'] ?? null,
            'supplier_reference_type' => $attributes['supplier_reference_type'] ?? 'lot_job',
            'supplier_reference_number' => $attributes['supplier_reference_number'] ?? null,
            'supplier_production_date' => $attributes['supplier_production_date'] ?? null,
            'machine_number' => $attributes['machine_number'] ?? null,
            'lot_or_job_number' => $attributes['lot_or_job_number'] ?? null,
            'time_on' => $attributes['time_on'] ?? null,
            'operator_name' => $attributes['operator_name'] ?? null,
            'linked_mo' => $attributes['linked_mo'] ?? null,
            'batch_record_id' => $attributes['batch_record_id'] ?? null,
            'linked_packing_run_id' => $attributes['linked_packing_run_id'] ?? null,
            'linked_drum_run_id' => $attributes['linked_drum_run_id'] ?? null,
        ]);
    }
}
