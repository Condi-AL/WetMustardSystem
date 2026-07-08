<?php

namespace App\Features\ManufacturingOrders;

use App\Domains\WinMan\Data\ManufacturingOrderData;
use App\Domains\WinMan\Jobs\SearchOutstandingManufacturingOrdersJob;

/**
 * Returns eligible outstanding WinMan MOs for the MO Search screen (scope §15).
 * DBMTS lists existing MOs only; it never creates WinMan MOs.
 */
class SearchManufacturingOrdersFeature
{
    public function __construct(
        private readonly SearchOutstandingManufacturingOrdersJob $searchOrders,
    ) {
    }

    /**
     * @return array<int, ManufacturingOrderData>
     */
    public function __invoke(?string $search = null, int $limit = 50): array
    {
        return ($this->searchOrders)($search, $limit);
    }
}
