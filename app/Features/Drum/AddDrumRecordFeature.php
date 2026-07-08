<?php

namespace App\Features\Drum;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Drum\Jobs\AddDrumRecordJob;
use App\Models\DrumProcessingPallet;
use App\Models\DrumRecord;
use App\Models\User;

/**
 * Adds an individual drum record to a drum processing pallet and audits it.
 */
class AddDrumRecordFeature
{
    public function __construct(
        private readonly AddDrumRecordJob $addDrumRecord,
        private readonly RecordAuditEntryJob $recordAuditEntry,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(DrumProcessingPallet $pallet, array $attributes, ?User $user = null): DrumRecord
    {
        $drum = ($this->addDrumRecord)($pallet, $attributes);

        ($this->recordAuditEntry)($drum, 'create', $user, 'drum_number', null, $drum->drum_number);

        return $drum;
    }
}
