<?php

namespace App\Http\Controllers;

use App\Features\Batches\ExportBatchRecordFeature;
use App\Models\BatchRecord;

class BatchRecordExportController extends Controller
{
    public function __invoke(BatchRecord $batch)
    {
        return $this->serve(ExportBatchRecordFeature::class, $batch);
    }
}
