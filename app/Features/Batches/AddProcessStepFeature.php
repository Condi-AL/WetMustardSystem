<?php

namespace App\Features\Batches;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Batch\Jobs\AddProcessStepJob;
use App\Models\BatchProcessStep;
use App\Models\BatchRecord;
use App\Models\User;

/**
 * Adds a process step to a batch and records an audit entry.
 */
class AddProcessStepFeature
{
    public function __construct(
        private readonly AddProcessStepJob $addProcessStep,
        private readonly RecordAuditEntryJob $recordAuditEntry,
    ) {
    }

    public function __invoke(
        BatchRecord $batch,
        string $stepName,
        ?int $sequence = null,
        bool $required = true,
        ?User $user = null,
    ): BatchProcessStep {
        $step = ($this->addProcessStep)($batch, $stepName, $sequence, $required);

        ($this->recordAuditEntry)($step, 'create', $user, 'step_name', null, $step->step_name);

        return $step;
    }
}
