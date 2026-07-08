<?php

namespace App\Features\Batches;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Batch\Jobs\AddIngredientLotJob;
use App\Models\BatchIngredientLot;
use App\Models\BatchRecord;
use App\Models\User;

/**
 * Adds an ingredient lot to a batch and records an audit entry.
 */
class AddIngredientLotFeature
{
    public function __construct(
        private readonly AddIngredientLotJob $addIngredientLot,
        private readonly RecordAuditEntryJob $recordAuditEntry,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(BatchRecord $batch, array $attributes, ?User $user = null): BatchIngredientLot
    {
        $lot = ($this->addIngredientLot)($batch, $attributes);

        ($this->recordAuditEntry)($lot, 'create', $user, 'lot_number', null, $lot->lot_number);

        return $lot;
    }
}
