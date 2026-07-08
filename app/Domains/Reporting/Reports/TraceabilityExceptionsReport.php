<?php

namespace App\Domains\Reporting\Reports;

use App\Models\BatchRecord;
use Carbon\CarbonInterface;

/**
 * Traceability exceptions - batches with missing mandatory traceability data
 * (report key: dbmts_traceability_exceptions).
 */
class TraceabilityExceptionsReport extends AbstractReport
{
    public const KEY = 'dbmts_traceability_exceptions';

    public function key(): string
    {
        return self::KEY;
    }

    public function name(): string
    {
        return 'Traceability Exceptions';
    }

    protected function data(CarbonInterface $from, CarbonInterface $to): array
    {
        $batches = BatchRecord::query()
            ->with(['ingredientLots', 'pallecons'])
            ->whereBetween('production_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $rows = [];
        foreach ($batches as $batch) {
            foreach ($batch->ingredientLots as $lot) {
                if (blank($lot->lot_number)) {
                    $rows[] = [$batch->batch_number, 'Ingredient missing lot number: '.($lot->material_description ?? $lot->material_code)];
                }
            }
            foreach ($batch->pallecons as $pallecon) {
                if (blank($pallecon->top_seal_number) && blank($pallecon->bottom_seal_number)) {
                    $rows[] = [$batch->batch_number, 'Pallecon #'.$pallecon->serial_number.' missing seal numbers'];
                }
            }
        }

        return [
            'headers' => ['Batch', 'Issue'],
            'rows' => $rows,
            'summary' => count($rows).' traceability gap(s) found.',
        ];
    }
}
