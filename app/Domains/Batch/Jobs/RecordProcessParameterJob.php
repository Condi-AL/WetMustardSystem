<?php

namespace App\Domains\Batch\Jobs;

use App\Models\BatchProcessParameter;
use App\Models\BatchProcessStep;
use App\Models\BatchRecord;
use App\Models\User;

/**
 * Records a process parameter value against a batch (optionally a step).
 */
class RecordProcessParameterJob
{
    public function __invoke(
        BatchRecord $batch,
        string $name,
        ?string $value,
        ?string $uom,
        User $user,
        ?BatchProcessStep $step = null,
    ): BatchProcessParameter {
        return $batch->processParameters()->create([
            'batch_process_step_id' => $step?->id,
            'parameter_name' => $name,
            'value' => $value,
            'uom' => $uom,
            'entered_by' => $user->id,
            'entered_at' => now(),
        ]);
    }
}
