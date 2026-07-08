<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * DBMTS Packaging Supplier Master.
 *
 * Supports both traditional lot/job supplier formats and NVE-number supplier
 * formats. reference_type is one of: 'lot_job' | 'nve'.
 */
class PackagingSupplier extends Model
{
    protected $fillable = [
        'supplier_name',
        'reference_type',
        'packaging_type',
        'active_flag',
    ];

    protected function casts(): array
    {
        return [
            'active_flag' => 'boolean',
        ];
    }
}
