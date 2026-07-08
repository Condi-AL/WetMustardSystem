<?php

namespace App\Features\Packaging;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Packaging\Jobs\AddPackagingLotJob;
use App\Models\PackagingLot;
use App\Models\User;

/**
 * Adds a packaging lot traceability record (bucket/lid/drum, lot/job or NVE) and
 * audits it.
 */
class AddPackagingLotFeature
{
    public function __construct(
        private readonly AddPackagingLotJob $addPackagingLot,
        private readonly RecordAuditEntryJob $recordAuditEntry,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes, ?User $user = null): PackagingLot
    {
        $lot = ($this->addPackagingLot)($attributes);

        ($this->recordAuditEntry)($lot, 'create', $user, 'supplier_reference_number', null, $lot->supplier_reference_number);

        return $lot;
    }
}
