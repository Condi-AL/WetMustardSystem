<?php

namespace App\Features\Pallet;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Pallet\Jobs\AddPalletRecordJob;
use App\Models\PalletRecord;
use App\Models\User;

/**
 * Adds a finished pallet record (for a packing run or a drum processing run)
 * and audits it.
 */
class AddPalletRecordFeature
{
    public function __construct(
        private readonly AddPalletRecordJob $addPalletRecord,
        private readonly RecordAuditEntryJob $recordAuditEntry,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes, ?User $user = null): PalletRecord
    {
        $pallet = ($this->addPalletRecord)($attributes);

        ($this->recordAuditEntry)($pallet, 'create', $user, 'pallet_number', null, $pallet->pallet_number);

        return $pallet;
    }
}
