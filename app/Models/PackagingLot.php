<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DBMTS Packaging Lot traceability (scope entity: PackagingLot).
 *
 * supplier_reference_type is one of 'lot_job' | 'nve'.
 */
class PackagingLot extends Model
{
    protected $fillable = [
        'packaging_type', 'supplier', 'supplier_reference_type', 'supplier_reference_number',
        'supplier_production_date', 'machine_number', 'lot_or_job_number', 'time_on', 'operator_name',
        'linked_mo', 'batch_record_id', 'linked_packing_run_id', 'linked_drum_run_id',
    ];

    protected function casts(): array
    {
        return [
            'supplier_production_date' => 'date',
            'time_on' => 'datetime',
        ];
    }

    public function batchRecord(): BelongsTo
    {
        return $this->belongsTo(BatchRecord::class);
    }
}
