<?php

namespace Tests\Feature\Batches;

use App\Features\Drum\AddDrumPalletFeature;
use App\Features\Drum\AddDrumRecordFeature;
use App\Features\Drum\CreateDrumProcessingRunFeature;
use App\Features\Packaging\AddPackagingLotFeature;
use App\Features\Packing\ConsumePalleconFeature;
use App\Features\Packing\CreatePackingRunFeature;
use App\Features\Packing\RecordPackingWeightCheckFeature;
use App\Models\BatchRecord;
use App\Models\ManufacturingOrder;
use App\Models\PackingWeightCheck;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackingDrumPackagingTest extends TestCase
{
    use RefreshDatabase;

    private function makeBatch(): BatchRecord
    {
        $product = Product::create(['recipe_code' => 'R1', 'product_name' => 'Test', 'active_flag' => true]);
        $order = ManufacturingOrder::create([
            'mo_number' => 'MO9',
            'winman_manufacturing_order' => 909,
            'winman_manufacturing_order_id' => 'MO9',
            'recipe_code' => 'R1',
            'product_id' => $product->id,
            'planned_quantity' => 500,
            'quantity_outstanding' => 500,
            'winman_system_type' => 'F',
            'status' => 'selected',
        ]);

        return BatchRecord::create([
            'manufacturing_order_id' => $order->id,
            'product_id' => $product->id,
            'batch_number' => 'WM260708-55',
            'production_date' => now()->toDateString(),
            'status' => BatchRecord::STATUS_IN_PROGRESS,
        ]);
    }

    public function test_packing_run_consumes_ibc_and_computes_weight_average(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch();

        $run = app(CreatePackingRunFeature::class)($batch, 'Day', $user);
        app(ConsumePalleconFeature::class)($run, ['source_batch_number' => $batch->batch_number], $user);

        $check = app(RecordPackingWeightCheckFeature::class)($run, [
            'weight_1' => 100, 'weight_2' => 102, 'weight_3' => 98,
        ], $user);

        $this->assertSame('100.000', (string) $check->average_weight);
        $this->assertSame(1, $run->ibcs()->count());
        $this->assertDatabaseHas('electronic_signatures', [
            'entity_name' => 'packing_weight_checks',
            'signature_purpose' => 'packing_weight_check',
        ]);
    }

    public function test_a_failing_weight_check_is_audited_as_a_breach(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch();
        $run = app(CreatePackingRunFeature::class)($batch, null, $user);

        app(RecordPackingWeightCheckFeature::class)($run, [
            'weight_1' => 90, 'result' => PackingWeightCheck::RESULT_FAIL,
        ], $user);

        $this->assertDatabaseHas('audit_trails', [
            'entity_name' => 'packing_weight_checks',
            'action' => 'packing_weight_breach',
        ]);
    }

    public function test_drum_run_captures_pallet_and_drum_records(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch();

        $run = app(CreateDrumProcessingRunFeature::class)($batch, ['operator' => 'Op1', 'bbe_matches_winman' => true], $user);
        $pallet = app(AddDrumPalletFeature::class)($run, ['pallet_ticket_number' => 'PT-1', 'pallecon_number' => 'PC-1'], $user);
        $drum = app(AddDrumRecordFeature::class)($pallet, [
            'drum_number' => 'D-001',
            'filler_weight' => 250,
            'bag_seal_number' => 'BAG-1',
            'drum_seal_number' => 'SEAL-1',
            'liner_clean_undamaged' => true,
        ], $user);

        $this->assertSame('D-001', $drum->drum_number);
        $this->assertSame(1, $run->pallets()->count());
        $this->assertDatabaseHas('electronic_signatures', [
            'entity_name' => 'drum_processing_pallets',
            'signature_purpose' => 'drum_pallet_checked',
        ]);
    }

    public function test_packaging_lot_supports_nve_format(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch();

        $lot = app(AddPackagingLotFeature::class)([
            'packaging_type' => 'Bucket',
            'supplier' => 'Jokey',
            'supplier_reference_type' => 'nve',
            'supplier_reference_number' => '340123450000000017',
            'batch_record_id' => $batch->id,
        ], $user);

        $this->assertSame('nve', $lot->supplier_reference_type);
        $this->assertDatabaseHas('packaging_lots', [
            'id' => $lot->id,
            'supplier_reference_number' => '340123450000000017',
        ]);
    }
}
