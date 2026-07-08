<?php

namespace App\Features\Packing;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Packing\Jobs\AddPackingRunIbcJob;
use App\Models\PackingRun;
use App\Models\PackingRunIbc;
use App\Models\User;

/**
 * Records consumption of a pallecon/IBC by a packing run (traceability link) and
 * audits it.
 */
class ConsumePalleconFeature
{
    public function __construct(
        private readonly AddPackingRunIbcJob $addPackingRunIbc,
        private readonly RecordAuditEntryJob $recordAuditEntry,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(PackingRun $run, array $attributes, ?User $user = null): PackingRunIbc
    {
        $ibc = ($this->addPackingRunIbc)($run, $attributes);

        ($this->recordAuditEntry)($ibc, 'create', $user, 'source_batch_number', null, $ibc->source_batch_number);

        return $ibc;
    }
}
