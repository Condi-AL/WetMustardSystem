<?php

namespace App\Features\Batches;

use App\Domains\Export\BatchRecordExporter;
use App\Models\BatchRecord;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Produces an audit-ready HTML export of a single batch record as a download
 * (scope §14.2 / §16 batch record export).
 */
class ExportBatchRecordFeature
{
    public function __construct(
        private readonly BatchRecordExporter $exporter,
    ) {
    }

    public function __invoke(BatchRecord $batch): Response
    {
        $html = $this->exporter->export($batch);
        $filename = 'batch-'.Str::slug($batch->batch_number).'.html';

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
