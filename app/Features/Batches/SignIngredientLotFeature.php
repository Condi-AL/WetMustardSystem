<?php

namespace App\Features\Batches;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Batch\Jobs\SignIngredientLotJob;
use App\Domains\Signature\Jobs\RecordElectronicSignatureJob;
use App\Models\BatchIngredientLot;
use App\Models\User;

/**
 * Applies a weighed or tipped sign-off to an ingredient lot, capturing an
 * electronic signature and an audit entry (scope §6 - every critical action is
 * timestamped and linked to a named user).
 */
class SignIngredientLotFeature
{
    public function __construct(
        private readonly SignIngredientLotJob $signIngredientLot,
        private readonly RecordElectronicSignatureJob $recordSignature,
        private readonly RecordAuditEntryJob $recordAuditEntry,
    ) {
    }

    public function __invoke(BatchIngredientLot $lot, string $purpose, User $user): BatchIngredientLot
    {
        $lot = ($this->signIngredientLot)($lot, $purpose, $user);

        $meaning = $purpose === SignIngredientLotJob::PURPOSE_WEIGHED
            ? 'Ingredient weighed and verified'
            : 'Ingredient tipped into batch';

        ($this->recordSignature)($lot, $purpose, $user, $meaning);
        ($this->recordAuditEntry)($lot, 'sign', $user, $purpose);

        return $lot;
    }
}
