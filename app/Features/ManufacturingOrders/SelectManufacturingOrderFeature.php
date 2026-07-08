<?php

namespace App\Features\ManufacturingOrders;

use App\Models\ManufacturingOrder;
use App\Models\User;
use App\Operations\SelectManufacturingOrderOperation;

/**
 * Selects an existing outstanding WinMan MO into DBMTS (scope acceptance
 * criteria 2-3). Delegates to SelectManufacturingOrderOperation so the same
 * selection logic is reused by the batch-start flow.
 *
 * DBMTS never creates WinMan MOs; this consumes an existing one only.
 */
class SelectManufacturingOrderFeature
{
    public function __construct(
        private readonly SelectManufacturingOrderOperation $selectManufacturingOrder,
    ) {
    }

    public function __invoke(int $winmanManufacturingOrder, ?User $user = null): ManufacturingOrder
    {
        return ($this->selectManufacturingOrder)($winmanManufacturingOrder, $user);
    }
}
