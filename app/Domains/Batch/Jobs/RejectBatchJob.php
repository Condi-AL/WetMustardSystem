<?php

namespace App\Domains\Batch\Jobs;

use App\Domains\Batch\Exceptions\BatchException;
use App\Models\BatchRecord;
use App\Models\User;

/**
 * QA rejection of a completed batch: transitions completed -> in_progress so
 * corrections can be made. A reason is required and recorded by the feature.
 */
class RejectBatchJob
{
    public function __invoke(BatchRecord $batch, User $user): BatchRecord
    {
        if ($batch->status !== BatchRecord::STATUS_COMPLETED) {
            throw new BatchException('Only completed batches can be rejected by QA.');
        }

        $batch->forceFill([
            'status' => BatchRecord::STATUS_IN_PROGRESS,
            'completed_by' => null,
            'completed_at' => null,
        ])->save();

        return $batch;
    }
}
