<?php

namespace App\Domains\Batch\Jobs;

use App\Models\BatchRecord;
use App\Models\User;

/**
 * Transitions a batch to completed with the completing user and timestamp.
 */
class CompleteBatchJob
{
    public function __invoke(BatchRecord $batch, User $user): BatchRecord
    {
        $batch->forceFill([
            'status' => BatchRecord::STATUS_COMPLETED,
            'completed_by' => $user->id,
            'completed_at' => now(),
        ])->save();

        return $batch;
    }
}
