<?php

namespace App\Domains\Reporting\Reports;

use App\Models\PackingWeightCheck;
use Carbon\CarbonInterface;

/**
 * Packing weight exceptions - failed weight checks (report key:
 * dbmts_weight_exceptions).
 */
class PackingWeightExceptionsReport extends AbstractReport
{
    public const KEY = 'dbmts_weight_exceptions';

    public function key(): string
    {
        return self::KEY;
    }

    public function name(): string
    {
        return 'Packing Weight Exceptions';
    }

    protected function data(CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = PackingWeightCheck::query()
            ->with('packingRun.batchRecord')
            ->where('result', PackingWeightCheck::RESULT_FAIL)
            ->whereBetween('check_time', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderByDesc('check_time')
            ->get()
            ->map(fn (PackingWeightCheck $c): array => [
                $c->packingRun?->batchRecord?->batch_number ?? '—',
                $c->check_time?->format('d M H:i') ?? '—',
                $c->average_weight ?? '—',
                'FAIL',
            ])
            ->all();

        return [
            'headers' => ['Batch', 'Time', 'Average weight', 'Result'],
            'rows' => $rows,
            'summary' => count($rows).' failed weight check(s).',
        ];
    }
}
