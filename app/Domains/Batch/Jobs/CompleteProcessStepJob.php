<?php

namespace App\Domains\Batch\Jobs;

use App\Models\BatchProcessStep;
use App\Models\User;

/**
 * Marks a process step complete with the completing user and timestamp.
 */
class CompleteProcessStepJob
{
    public function __invoke(BatchProcessStep $step, User $user, ?string $comments = null): BatchProcessStep
    {
        $step->forceFill([
            'completed_by' => $user->id,
            'completed_at' => now(),
            'comments' => $comments,
        ])->save();

        return $step;
    }
}
