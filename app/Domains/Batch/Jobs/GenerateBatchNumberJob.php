<?php

namespace App\Domains\Batch\Jobs;

use App\Models\BatchRecord;
use Illuminate\Support\Carbon;

/**
 * Generates a unique DBMTS batch number of the form WM{yymmdd}-{nn}.
 *
 * The daily sequence increments per production date; uniqueness is guaranteed
 * against the batch_records table.
 */
class GenerateBatchNumberJob
{
    public function __invoke(?Carbon $productionDate = null): string
    {
        $date = ($productionDate ?? now())->format('ymd');
        $prefix = "WM{$date}";

        $sequence = BatchRecord::query()
            ->where('batch_number', 'like', "{$prefix}-%")
            ->count() + 1;

        do {
            $candidate = sprintf('%s-%02d', $prefix, $sequence);
            $sequence++;
        } while (BatchRecord::query()->where('batch_number', $candidate)->exists());

        return $candidate;
    }
}
