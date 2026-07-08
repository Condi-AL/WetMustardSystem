<?php

namespace Tests\Feature\Traceability;

use App\Features\Traceability\BackwardTraceFeature;
use App\Features\Traceability\ForwardTraceFeature;
use App\Models\BatchIngredientLot;
use App\Models\BatchRecord;
use App\Models\ManufacturingOrder;
use App\Models\PackagingLot;
use App\Models\PackingRun;
use App\Models\PalleconRecord;
use App\Models\PalletRecord;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraceabilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeTracedBatch(): BatchRecord
    {
        $product = Product::create(['recipe_code' => 'R1', 'product_name' => 'Traced Mustard', 'active_flag' => true]);
        $order = ManufacturingOrder::create([
            'mo_number' => 'MO-TRACE',
            'winman_manufacturing_order' => 700,
            'winman_manufacturing_order_id' => 'MO-TRACE',
            'recipe_code' => 'R1',
            'product_id' => $product->id,
            'planned_quantity' => 500,
            'quantity_outstanding' => 500,
            'winman_system_type' => 'F',
            'status' => 'selected',
        ]);
        $batch = BatchRecord::create([
            'manufacturing_order_id' => $order->id,
            'product_id' => $product->id,
            'batch_number' => 'WM260708-TR',
            'production_date' => now()->toDateString(),
            'status' => BatchRecord::STATUS_IN_PROGRESS,
        ]);

        BatchIngredientLot::create([
            'batch_record_id' => $batch->id,
            'material_description' => 'Spirit Vinegar',
            'lot_number' => 'ING-123',
            'actual_quantity' => 100,
        ]);
        PalleconRecord::create(['batch_record_id' => $batch->id, 'serial_number' => 'PC-9', 'fill_weight' => 800]);

        $run = PackingRun::create([
            'batch_record_id' => $batch->id,
            'packing_date' => now()->toDateString(),
            'status' => 'open',
        ]);
        PalletRecord::create(['packing_run_id' => $run->id, 'pallet_number' => 'PAL-1', 'pallet_amount' => 40]);

        PackagingLot::create([
            'batch_record_id' => $batch->id,
            'packaging_type' => 'Bucket',
            'supplier_reference_type' => 'nve',
            'supplier_reference_number' => 'NVE-777',
        ]);

        return $batch;
    }

    public function test_backward_trace_from_finished_pallet_returns_the_batch(): void
    {
        $batch = $this->makeTracedBatch();

        $results = app(BackwardTraceFeature::class)('PAL-1');

        $this->assertCount(1, $results);
        $this->assertSame($batch->id, $results[0]['batch']->id);
        $this->assertSame('Finished pallet', $results[0]['matches'][0]['on']);
        $this->assertCount(1, $results[0]['batch']->ingredientLots);
    }

    public function test_backward_trace_by_batch_number(): void
    {
        $batch = $this->makeTracedBatch();

        $results = app(BackwardTraceFeature::class)('WM260708-TR');

        $this->assertCount(1, $results);
        $this->assertSame($batch->id, $results[0]['batch']->id);
    }

    public function test_backward_trace_by_pallecon_serial(): void
    {
        $batch = $this->makeTracedBatch();

        $results = app(BackwardTraceFeature::class)('PC-9');

        $this->assertCount(1, $results);
        $this->assertSame($batch->id, $results[0]['batch']->id);
    }

    public function test_forward_trace_from_ingredient_lot(): void
    {
        $batch = $this->makeTracedBatch();

        $results = app(ForwardTraceFeature::class)('ING-123');

        $this->assertCount(1, $results);
        $this->assertSame($batch->id, $results[0]['batch']->id);
    }

    public function test_forward_trace_from_packaging_nve(): void
    {
        $batch = $this->makeTracedBatch();

        $results = app(ForwardTraceFeature::class)('NVE-777');

        $this->assertCount(1, $results);
        $this->assertSame($batch->id, $results[0]['batch']->id);
    }

    public function test_unknown_term_returns_no_results(): void
    {
        $this->makeTracedBatch();

        $this->assertSame([], app(BackwardTraceFeature::class)('does-not-exist'));
    }
}
