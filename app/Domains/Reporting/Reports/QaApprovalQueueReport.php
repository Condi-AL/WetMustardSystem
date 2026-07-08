<?php

namespace App\Domains\Reporting\Reports;

use App\Models\BatchRecord;
use Carbon\CarbonInterface;

/**
 * Records awaiting QA approval (report key: dbmts_qa_approval_queue).
 */
class QaApprovalQueueReport extends AbstractReport
{
    public const KEY = 'dbmts_qa_approval_queue';

    public function key(): string
    {
        return self::KEY;
    }

    public function name(): string
    {
        return 'Records Awaiting QA Approval';
    }

    protected function data(CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = BatchRecord::query()
            ->with('product')
            ->where('status', BatchRecord::STATUS_COMPLETED)
            ->orderBy('completed_at')
            ->get()
            ->map(fn (BatchRecord $b): array => [
                $b->batch_number,
                $b->product?->product_name ?? '—',
                $b->completed_at?->format('d M H:i') ?? '—',
            ])
            ->all();

        return [
            'headers' => ['Batch', 'Product', 'Completed'],
            'rows' => $rows,
            'summary' => count($rows).' record(s) awaiting QA approval.',
        ];
    }
}
