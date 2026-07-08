<?php

namespace App\Features\Packing;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Packing\Jobs\CreatePackingRunJob;
use App\Models\BatchRecord;
use App\Models\PackingRun;
use App\Models\User;

/**
 * Creates a bucket packing run for a batch (Route A) with an audit entry.
 */
class CreatePackingRunFeature
{
    public function __construct(
        private readonly CreatePackingRunJob $createPackingRun,
        private readonly RecordAuditEntryJob $recordAuditEntry,
    ) {
    }

    public function __invoke(BatchRecord $batch, ?string $shift = null, ?User $user = null): PackingRun
    {
        $run = ($this->createPackingRun)($batch, $shift, $user);

        ($this->recordAuditEntry)($run, 'create', $user, 'status', null, $run->status);

        return $run;
    }
}
