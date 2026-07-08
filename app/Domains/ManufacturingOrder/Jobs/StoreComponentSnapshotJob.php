<?php

namespace App\Domains\ManufacturingOrder\Jobs;

use App\Domains\WinMan\Data\ComponentData;
use App\Models\ManufacturingOrder;
use App\Models\WinManMoComponentSnapshot;
use Illuminate\Support\Facades\DB;

/**
 * Stores a fresh WinMan component snapshot for a manufacturing order.
 *
 * A snapshot represents the live BOM at a point in time, so any previous
 * snapshot rows for the MO are replaced (scope §11.3: stored "when a batch
 * record is created or refreshed").
 *
 * @param  array<int, ComponentData>  $components
 */
class StoreComponentSnapshotJob
{
    /**
     * @param  array<int, ComponentData>  $components
     */
    public function __invoke(ManufacturingOrder $order, array $components): int
    {
        return DB::transaction(function () use ($order, $components): int {
            $order->componentSnapshots()->delete();

            $now = now();
            $rows = array_map(
                static fn (ComponentData $component): array => [
                    'manufacturing_order_id' => $order->id,
                    'winman_manufacturing_order' => $order->winman_manufacturing_order,
                    'winman_work_in_progress' => $component->winmanWorkInProgress,
                    'item_type' => $component->itemType,
                    'winman_component_product' => (string) $component->winmanComponentProduct,
                    'winman_component_product_id' => $component->winmanComponentProductId,
                    'component_description' => $component->componentDescription,
                    'classification' => $component->classification !== null ? (string) $component->classification : null,
                    'quantity' => $component->quantity,
                    'quantity_issued' => $component->quantityIssued,
                    'quantity_outstanding' => $component->quantityOutstanding,
                    'snapshot_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                $components,
            );

            if ($rows !== []) {
                WinManMoComponentSnapshot::insert($rows);
            }

            return count($rows);
        });
    }
}
