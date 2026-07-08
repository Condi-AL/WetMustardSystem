<?php

namespace Tests\Feature\Export;

use App\Features\Audit\ExportAuditTrailCsvFeature;
use App\Features\Audit\GenerateAuditTrailReportFeature;
use App\Features\Batches\ExportBatchRecordFeature;
use App\Models\AuditTrail;
use App\Models\BatchIngredientLot;
use App\Models\BatchRecord;
use App\Models\ManufacturingOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class ExportsTest extends TestCase
{
    use RefreshDatabase;

    private function makeBatch(): BatchRecord
    {
        $product = Product::create(['recipe_code' => 'R1', 'product_name' => 'Export Mustard', 'active_flag' => true]);
        $order = ManufacturingOrder::create([
            'mo_number' => 'MO-X', 'winman_manufacturing_order' => 1, 'winman_manufacturing_order_id' => 'MO-X',
            'recipe_code' => 'R1', 'product_id' => $product->id, 'planned_quantity' => 1, 'quantity_outstanding' => 1,
            'winman_system_type' => 'F', 'status' => 'selected',
        ]);
        $batch = BatchRecord::create([
            'manufacturing_order_id' => $order->id, 'product_id' => $product->id, 'batch_number' => 'WM-EXP-1',
            'production_date' => now()->toDateString(), 'status' => BatchRecord::STATUS_COMPLETED,
        ]);
        BatchIngredientLot::create(['batch_record_id' => $batch->id, 'material_description' => 'Water', 'lot_number' => 'LOT-Z', 'actual_quantity' => 10]);

        return $batch;
    }

    public function test_batch_record_export_returns_audit_ready_html_download(): void
    {
        $batch = $this->makeBatch();

        $response = app(ExportBatchRecordFeature::class)($batch);
        $content = $response->getContent();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('WM-EXP-1', $content);
        $this->assertStringContainsString('Electronic Signatures', $content);
        $this->assertStringContainsString('LOT-Z', $content);
    }

    public function test_batch_export_route_is_reachable(): void
    {
        $batch = $this->makeBatch();

        $this->actingAs(User::factory()->create())
            ->get(route('batches.export', $batch))
            ->assertOk();
    }

    public function test_audit_trail_report_filters_by_entity(): void
    {
        AuditTrail::create(['entity_name' => 'batch_records', 'entity_id' => 1, 'action' => 'complete']);
        AuditTrail::create(['entity_name' => 'metal_detector_checks', 'entity_id' => 1, 'action' => 'ccp_failure']);

        $entries = app(GenerateAuditTrailReportFeature::class)(['entity_name' => 'batch_records']);

        $this->assertCount(1, $entries);
        $this->assertSame('batch_records', $entries->first()->entity_name);
    }

    public function test_audit_trail_csv_export_streams(): void
    {
        AuditTrail::create(['entity_name' => 'batch_records', 'entity_id' => 1, 'action' => 'complete']);

        $response = app(ExportAuditTrailCsvFeature::class)([]);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }
}
