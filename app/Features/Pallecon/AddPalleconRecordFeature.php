<?php

namespace App\Features\Pallecon;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Pallecon\Jobs\AddPalleconRecordJob;
use App\Domains\Signature\Jobs\RecordElectronicSignatureJob;
use App\Models\BatchRecord;
use App\Models\PalleconRecord;
use App\Models\User;

/**
 * Adds a pallecon filling record to a batch, capturing a checked-by signature
 * and an audit entry.
 */
class AddPalleconRecordFeature
{
    public function __construct(
        private readonly AddPalleconRecordJob $addPalleconRecord,
        private readonly RecordElectronicSignatureJob $recordSignature,
        private readonly RecordAuditEntryJob $recordAuditEntry,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(BatchRecord $batch, array $attributes, User $user): PalleconRecord
    {
        $pallecon = ($this->addPalleconRecord)($batch, $attributes, $user);

        ($this->recordSignature)($pallecon, 'pallecon_checked', $user, 'Pallecon filled and checked');
        ($this->recordAuditEntry)($pallecon, 'create', $user, 'serial_number', null, $pallecon->serial_number);

        return $pallecon;
    }
}
