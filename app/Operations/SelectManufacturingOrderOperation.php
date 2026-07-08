<?php

namespace App\Operations;

use App\Domains\ManufacturingOrder\Jobs\StoreComponentSnapshotJob;
use App\Domains\ManufacturingOrder\Jobs\StoreSelectedManufacturingOrderJob;
use App\Domains\WinMan\Exceptions\WinManException;
use App\Domains\WinMan\Jobs\FetchManufacturingOrderComponentsJob;
use App\Domains\WinMan\Jobs\FetchManufacturingOrderJob;
use App\Models\ManufacturingOrder;
use App\Models\User;

/**
 * Selects an existing outstanding WinMan MO into DBMTS.
 *
 * Re-reads the MO authoritatively, validates eligibility (exists, outstanding
 * quantity, selectable system type), stores the local MO record and captures a
 * live component snapshot. Reused wherever an MO must be consumed (standalone
 * selection and batch start). DBMTS never creates WinMan MOs.
 */
class SelectManufacturingOrderOperation
{
    public function __construct(
        private readonly FetchManufacturingOrderJob $fetchManufacturingOrder,
        private readonly FetchManufacturingOrderComponentsJob $fetchComponents,
        private readonly StoreSelectedManufacturingOrderJob $storeManufacturingOrder,
        private readonly StoreComponentSnapshotJob $storeComponentSnapshot,
    ) {
    }

    public function __invoke(int $winmanManufacturingOrder, ?User $user = null): ManufacturingOrder
    {
        $data = ($this->fetchManufacturingOrder)($winmanManufacturingOrder);

        if ($data === null) {
            throw new WinManException(
                "WinMan manufacturing order {$winmanManufacturingOrder} does not exist."
            );
        }

        if ($data->quantityOutstanding <= 0) {
            throw new WinManException(
                "WinMan manufacturing order {$data->winmanManufacturingOrderId} has no outstanding quantity."
            );
        }

        $eligibleTypes = (array) config('winman.eligible_system_types', ['F', 'I', 'R']);
        if (! in_array($data->systemType, $eligibleTypes, true)) {
            throw new WinManException(
                "WinMan manufacturing order {$data->winmanManufacturingOrderId} is not in a selectable state ({$data->systemType})."
            );
        }

        $components = ($this->fetchComponents)($winmanManufacturingOrder);

        $order = ($this->storeManufacturingOrder)($data, $user);
        ($this->storeComponentSnapshot)($order, $components);

        return $order->load('componentSnapshots');
    }
}
