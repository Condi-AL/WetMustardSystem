<?php

namespace App\Domains\Packing\Jobs;

use App\Models\BatchRecord;
use App\Models\PackingRun;
use App\Models\User;

/**
 * Creates a bucket packing run against a batch (Route A), inheriting MO and
 * product from the batch's manufacturing order.
 */
class CreatePackingRunJob
{
    public function __invoke(BatchRecord $batch, ?string $shift = null, ?User $user = null): PackingRun
    {
        return PackingRun::create([
            'batch_record_id' => $batch->id,
            'product_id' => $batch->product_id,
            'mo_number' => $batch->manufacturingOrder?->mo_number,
            'packing_date' => now()->toDateString(),
            'shift' => $shift,
            'status' => PackingRun::STATUS_OPEN,
            'created_by' => $user?->id,
        ]);
    }
}
