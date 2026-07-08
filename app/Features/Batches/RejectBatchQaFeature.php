<?php

namespace App\Features\Batches;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Batch\Jobs\RejectBatchJob;
use App\Models\BatchRecord;
use App\Models\User;

/**
 * QA rejection of a completed batch: returns it to in_progress for correction
 * and records the reason in the audit trail (scope §11 - corrections require a
 * reason).
 */
class RejectBatchQaFeature
{
    public function __construct(
        private readonly RejectBatchJob $rejectBatch,
        private readonly RecordAuditEntryJob $recordAuditEntry,
    ) {
    }

    public function __invoke(BatchRecord $batch, User $user, string $reason): BatchRecord
    {
        $previousStatus = $batch->status;
        $batch = ($this->rejectBatch)($batch, $user);

        ($this->recordAuditEntry)($batch, 'reject', $user, 'status', $previousStatus, $batch->status, $reason);

        return $batch;
    }
}
