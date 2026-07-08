<?php

namespace App\Features\Batches;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Batch\Jobs\ApproveBatchJob;
use App\Domains\Signature\Jobs\RecordElectronicSignatureJob;
use App\Models\BatchRecord;
use App\Models\User;

/**
 * QA approval of a completed batch (scope §12 QA/Technical): transitions the
 * batch to closed with a QA electronic signature and audit entry.
 */
class ApproveBatchQaFeature
{
    public function __construct(
        private readonly ApproveBatchJob $approveBatch,
        private readonly RecordElectronicSignatureJob $recordSignature,
        private readonly RecordAuditEntryJob $recordAuditEntry,
    ) {
    }

    public function __invoke(BatchRecord $batch, User $user): BatchRecord
    {
        $previousStatus = $batch->status;
        $batch = ($this->approveBatch)($batch, $user);

        ($this->recordSignature)($batch, 'qa_approval', $user, 'Batch reviewed and released by QA');
        ($this->recordAuditEntry)($batch, 'approve', $user, 'status', $previousStatus, $batch->status);

        return $batch;
    }
}
