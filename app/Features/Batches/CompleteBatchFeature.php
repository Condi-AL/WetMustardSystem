<?php

namespace App\Features\Batches;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Batch\Exceptions\BatchException;
use App\Domains\Batch\Jobs\CompleteBatchJob;
use App\Domains\Batch\Jobs\ValidateBatchCompletionJob;
use App\Domains\Signature\Jobs\RecordElectronicSignatureJob;
use App\Models\BatchRecord;
use App\Models\User;

/**
 * Completes a batch after validating all mandatory data is present (scope
 * acceptance criterion 8). Blocks completion where mandatory lots, quantities,
 * parameters or signatures are missing, and captures a completion signature and
 * audit entry.
 */
class CompleteBatchFeature
{
    public function __construct(
        private readonly ValidateBatchCompletionJob $validateBatchCompletion,
        private readonly CompleteBatchJob $completeBatch,
        private readonly RecordElectronicSignatureJob $recordSignature,
        private readonly RecordAuditEntryJob $recordAuditEntry,
    ) {
    }

    public function __invoke(BatchRecord $batch, User $user): BatchRecord
    {
        $issues = ($this->validateBatchCompletion)($batch);

        if ($issues !== []) {
            throw BatchException::withIssues($issues);
        }

        $previousStatus = $batch->status;
        $batch = ($this->completeBatch)($batch, $user);

        ($this->recordSignature)($batch, 'batch_complete', $user, 'Batch completed and released for QA review');
        ($this->recordAuditEntry)($batch, 'complete', $user, 'status', $previousStatus, $batch->status);

        return $batch;
    }
}
