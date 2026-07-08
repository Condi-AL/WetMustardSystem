<?php

namespace App\Domains\Reporting\Reports;

use App\Models\BatchRecord;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

/**
 * Daily production summary (report key: dbmts_daily_production_summary).
 */
class DailyProductionSummaryReport extends AbstractReport
{
    public const KEY = 'dbmts_daily_production_summary';

    public function key(): string
    {
        return self::KEY;
    }

    public function name(): string
    {
        return 'Daily Production Summary';
    }

    protected function data(CarbonInterface $from, CarbonInterface $to): array
    {
        $batches = BatchRecord::query()
            ->with(['product', 'pallecons', 'ingredientLots'])
            ->whereBetween('production_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('production_date')
            ->get();

        $rows = $batches->map(fn (BatchRecord $b): array => [
            $b->batch_number,
            $b->product?->product_name ?? '—',
            Str::headline($b->status),
            $b->pallecons->count(),
            $b->ingredientLots->count(),
        ])->all();

        return [
            'headers' => ['Batch', 'Product', 'Status', 'Pallecons', 'Ingredient lots'],
            'rows' => $rows,
            'summary' => $batches->count().' batch(es) produced in period.',
        ];
    }
}
