<?php

namespace App\Features\Batches;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Batch\Jobs\CompleteProcessStepJob;
use App\Domains\Signature\Jobs\RecordElectronicSignatureJob;
use App\Models\BatchProcessStep;
use App\Models\User;

/**
 * Completes a process step with a mandatory sign-off, capturing an electronic
 * signature and an audit entry.
 */
class CompleteProcessStepFeature
{
    public function __construct(
        private readonly CompleteProcessStepJob $completeProcessStep,
        private readonly RecordElectronicSignatureJob $recordSignature,
        private readonly RecordAuditEntryJob $recordAuditEntry,
    ) {
    }

    public function __invoke(BatchProcessStep $step, User $user, ?string $comments = null): BatchProcessStep
    {
        $step = ($this->completeProcessStep)($step, $user, $comments);

        ($this->recordSignature)($step, 'step_complete', $user, "Process step '{$step->step_name}' completed", $comments);
        ($this->recordAuditEntry)($step, 'complete', $user, 'completed_at');

        return $step;
    }
}
