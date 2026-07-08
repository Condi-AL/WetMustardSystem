<?php

namespace App\Domains\Batch\Jobs;

use App\Models\BatchProcessStep;
use App\Models\BatchRecord;

/**
 * Adds a process step to a batch.
 */
class AddProcessStepJob
{
    public function __invoke(
        BatchRecord $batch,
        string $stepName,
        ?int $sequence = null,
        bool $required = true,
    ): BatchProcessStep {
        return $batch->processSteps()->create([
            'step_name' => $stepName,
            'sequence' => $sequence,
            'required_flag' => $required,
        ]);
    }
}
