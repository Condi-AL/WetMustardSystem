<?php

namespace App\Features\Traceability;

use App\Domains\Traceability\Jobs\FindBatchesByIdentifierJob;
use App\Domains\Traceability\Jobs\LoadBatchGenealogyJob;

/**
 * Backward trace (scope §13.1): from a finished pallet/drum/ticket/pallecon/
 * batch/MO identifier back to the manufacturing batch and its ingredient and
 * packaging lots.
 *
 * @return array<int, array{batch: \App\Models\BatchRecord, matches: array<int, array{on: string, value: ?string}>}>
 */
class BackwardTraceFeature
{
    public function __construct(
        private readonly FindBatchesByIdentifierJob $findBatches,
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
