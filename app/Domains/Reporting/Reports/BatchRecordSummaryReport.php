<?php

namespace App\Domains\Reporting\Reports;

use App\Models\BatchRecord;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

/**
 * Batch record summary by date range (report key: dbmts_batch_summary,
 * scope §14.2).
 */
class BatchRecordSummaryReport extends AbstractReport
{
    public const KEY = 'dbmts_batch_summary';

    public function key(): string
    {
        return self::KEY;
    }

    public function name(): string
    {
        return 'Batch Record Summary by Date Range';
    }

    protected function data(CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = BatchRecord::query()
            ->with('product')
            ->whereBetween('production_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('production_date')
            ->get()
            ->map(fn (BatchRecord $b): array => [
                $b->batch_number,
                $b->product?->product_name ?? '—',
                Str::headline($b->status),
                $b->production_date?->toDateString() ?? '—',
            ])
            ->all();

        return [
            'headers' => ['Batch', 'Product', 'Status', 'Production date'],
            'rows' => $rows,
            'summary' => count($rows).' batch record(s) in range.',
        ];
    }
}
