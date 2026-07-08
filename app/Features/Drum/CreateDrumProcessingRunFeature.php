<?php

namespace App\Features\Drum;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Drum\Jobs\CreateDrumProcessingRunJob;
use App\Models\BatchRecord;
use App\Models\DrumProcessingRun;
use App\Models\User;

/**
 * Creates a drum processing run for a batch (Route B) with an audit entry.
 */
class CreateDrumProcessingRunFeature
{
    public function __construct(
        private readonly CreateDrumProcessingRunJob $createDrumProcessingRun,
        private readonly RecordAuditEntryJob $recordAuditEntry,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(BatchRecord $batch, array $attributes = [], ?User $user = null): DrumProcessingRun
    {
        $run = ($this->createDrumProcessingRun)($batch, $attributes, $user);

        ($this->recordAuditEntry)($run, 'create', $user, 'status', null, $run->status);

        return $run;
    }
}
