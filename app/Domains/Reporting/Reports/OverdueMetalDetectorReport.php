<?php

namespace App\Domains\Reporting\Reports;

use App\Models\BatchRecord;
use Carbon\CarbonInterface;

/**
 * In-progress batches with an overdue or missing metal detector check
 * (report key: dbmts_overdue_metal_detect).
 */
class OverdueMetalDetectorReport extends AbstractReport
{
    public const KEY = 'dbmts_overdue_metal_detect';

    private const OVERDUE_HOURS = 2;

    public function key(): string
    {
        return self::KEY;
    }

    public function name(): string
    {
        return 'Overdue Metal Detector Checks';
    }

    protected function data(CarbonInterface $from, CarbonInterface $to): array
    {
        $threshold = now()->subHours(self::OVERDUE_HOURS);

        $rows = BatchRecord::query()
            ->with('metalDetectorChecks')
            ->where('status', BatchRecord::STATUS_IN_PROGRESS)
            ->get()
            ->filter(function (BatchRecord $b) use ($threshold): bool {
                $last = $b->metalDetectorChecks->max('check_time');

                return $last === null || $last->lt($threshold);
            })
            ->map(fn (BatchRecord $b): array => [
                $b->batch_number,
                $b->metalDetectorChecks->max('check_time')?->format('d M H:i') ?? 'never',
                $b->metalDetectorChecks->count(),
            ])
            ->values()
            ->all();

        return [
            'headers' => ['Batch', 'Last check', 'Total checks'],
            'rows' => $rows,
            'summary' => count($rows).' batch(es) with an overdue metal detector check (>'.self::OVERDUE_HOURS.'h).',
        ];
    }
}
