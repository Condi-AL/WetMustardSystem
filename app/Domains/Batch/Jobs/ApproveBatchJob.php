<?php

namespace App\Domains\Batch\Jobs;

use App\Domains\Batch\Exceptions\BatchException;
use App\Models\BatchRecord;
use App\Models\User;

/**
 * QA approval of a completed batch: transitions completed -> closed.
 */
class ApproveBatchJob
{
    public function __invoke(BatchRecord $batch, User $user): BatchRecord
    {
        if ($batch->status !== BatchRecord::STATUS_COMPLETED) {
            throw new BatchException('Only completed batches can be approved by QA.');
        }

        $batch->forceFill([
            'status' => BatchRecord::STATUS_CLOSED,
        ])->save();

        return $batch;
    }
}
