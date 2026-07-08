<?php

namespace App\Features\Traceability;

use App\Domains\Traceability\Jobs\FindBatchesByLotJob;
use App\Domains\Traceability\Jobs\LoadBatchGenealogyJob;

/**
 * Forward trace (scope §13.2): from an ingredient or packaging lot to the
 * batches impacted and their downstream pallecons, pallets and drums.
 *
 * @return array<int, array{batch: \App\Models\BatchRecord, matches: array<int, array{on: string, value: ?string}>}>
 */
class ForwardTraceFeature
{
    public function __construct(
        private readonly FindBatchesByLotJob $findBatches,
        private readonly LoadBatchGenealogyJob $loadGenealogy,
    ) {
    }

    /**
     * @return array<int, array{batch: \App\Models\BatchRecord, matches: array<int, array{on: string, value: ?string}>}>
     */
    public function __invoke(string $term): array
    {
        $results = ($this->findBatches)($term);

        foreach ($results as &$result) {
            $result['batch'] = ($this->loadGenealogy)($result['batch']);
        }

        return $results;
    }
}
