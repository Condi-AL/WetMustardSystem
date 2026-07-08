<?php

namespace App\Domains\Reporting\Reports;

use App\Models\BatchRecord;
use Carbon\CarbonInterface;

/**
 * Open / incomplete batch records (report key: dbmts_open_batches).
 */
class OpenBatchesReport extends AbstractReport
{
    public const KEY = 'dbmts_open_batches';

    public function key(): string
    {
        return self::KEY;
    }

    public function name(): string
    {
        return 'Open / Incomplete Batch Records';
    }

    protected function data(CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = BatchRecord::query()
            ->with(['product', 'manufacturingOrder'])
            ->where('status', BatchRecord::STATUS_IN_PROGRESS)
            ->where('created_at', '<=', $to->copy()->endOfDay())
            ->orderBy('created_at')
            ->get()
            ->map(fn (BatchRecord $b): array => [
                $b->batch_number,
                $b->product?->product_name ?? '—',
                $b->manufacturingOrder?->mo_number ?? '—',
                $b->created_at?->diffForHumans() ?? '—',
            ])
            ->all();

        return [
            'headers' => ['Batch', 'Product', 'MO', 'Age'],
            'rows' => $rows,
            'summary' => count($rows).' open batch record(s).',
        ];
    }
}
