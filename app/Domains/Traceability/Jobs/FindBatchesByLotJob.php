<?php

namespace App\Domains\Traceability\Jobs;

use App\Models\BatchIngredientLot;
use App\Models\BatchRecord;
use App\Models\PackagingLot;

/**
 * Forward trace resolver: maps an ingredient lot number or packaging supplier
 * reference (lot/job or NVE) to the batch record(s) impacted, with match
 * context.
 *
 * @return array<int, array{batch: BatchRecord, matches: array<int, array{on: string, value: ?string}>}>
 */
class FindBatchesByLotJob
{
    public function __invoke(string $term): array
    {
        $like = '%'.trim($term).'%';

        /** @var array<int, array{matches: array<int, array{on: string, value: ?string}>}> $found */
        $found = [];
        $register = function (?int $batchId, string $on, ?string $value) use (&$found): void {
            if ($batchId === null) {
                return;
            }
            $found[$batchId]['matches'][] = ['on' => $on, 'value' => $value];
        };

        BatchIngredientLot::query()
            ->where('lot_number', 'like', $like)
            ->get(['batch_record_id', 'material_description', 'lot_number'])
            ->each(fn (BatchIngredientLot $l) => $register(
                $l->batch_record_id,
                'Ingredient lot ('.($l->material_description ?? 'material').')',
                $l->lot_number,
            ));

        PackagingLot::query()
            ->whereNotNull('batch_record_id')
            ->where(function ($query) use ($like): void {
                $query->where('supplier_reference_number', 'like', $like)
                    ->orWhere('lot_or_job_number', 'like', $like);
            })
            ->get(['batch_record_id', 'packaging_type', 'supplier_reference_number', 'lot_or_job_number'])
            ->each(fn (PackagingLot $p) => $register(
                $p->batch_record_id,
                'Packaging lot ('.$p->packaging_type.')',
                $p->supplier_reference_number ?? $p->lot_or_job_number,
            ));

        $batches = BatchRecord::query()->whereIn('id', array_keys($found))->get()->keyBy('id');

        $result = [];
        foreach ($found as $batchId => $data) {
            if ($batch = $batches->get($batchId)) {
                $result[] = ['batch' => $batch, 'matches' => $data['matches']];
            }
        }

        return $result;
    }
}
