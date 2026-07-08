<?php

namespace App\Domains\Reporting\Reports;

use App\Models\DrumProcessingRun;
use Carbon\CarbonInterface;

/**
 * Drum processing daily summary (report key: dbmts_drum_summary).
 */
class DrumProcessingSummaryReport extends AbstractReport
{
    public const KEY = 'dbmts_drum_summary';

    public function key(): string
    {
        return self::KEY;
    }

    public function name(): string
    {
        return 'Drum Processing Daily Summary';
    }

    protected function data(CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = DrumProcessingRun::query()
            ->with(['batchRecord', 'pallets.drumRecords'])
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->get()
            ->map(fn (DrumProcessingRun $r): array => [
                $r->batchRecord?->batch_number ?? '—',
                $r->operator ?? '—',
                $r->pallets->count(),
                $r->pallets->sum(fn ($p) => $p->drumRecords->count()),
            ])
            ->all();

        return [
            'headers' => ['Batch', 'Operator', 'Pallets', 'Drums'],
            'rows' => $rows,
            'summary' => count($rows).' drum run(s) in period.',
        ];
    }
}
