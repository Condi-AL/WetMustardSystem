<?php

namespace Tests\Feature\Batches;

use App\Domains\Batch\Exceptions\BatchException;
use App\Features\Batches\ApproveBatchQaFeature;
use App\Features\Batches\RejectBatchQaFeature;
use App\Features\MetalDetector\RecordMetalDetectorCheckFeature;
use App\Features\Pallecon\AddPalleconRecordFeature;
use App\Models\BatchRecord;
use App\Models\ManufacturingOrder;
use App\Models\MetalDetectorCheck;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetalDetectorAndQaTest extends TestCase
{
    use RefreshDatabase;

    private function makeBatch(string $status = BatchRecord::STATUS_IN_PROGRESS): BatchRecord
    {
        $product = Product::create(['recipe_code' => 'R1', 'product_name' => 'Test', 'active_flag' => true]);
        $order = ManufacturingOrder::create([
            'mo_number' => 'MO1',
            'winman_manufacturing_order' => 222,
            'winman_manufacturing_order_id' => 'MO1',
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
            'batch_number' => 'WM260708-77',
            'production_date' => now()->toDateString(),
            'status' => $status,
        ]);
    }

    public function test_a_passing_metal_detector_check_is_recorded_with_a_signature(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch();

        $check = app(RecordMetalDetectorCheckFeature::class)($batch, [
            'check_type' => MetalDetectorCheck::TYPE_HOURLY,
            'fe10_pass' => true,
            'non_fe15_pass' => true,
            'ss20_pass' => true,
        ], $user);

        $this->assertSame(MetalDetectorCheck::RESULT_PASS, $check->overall_result);
        $this->assertDatabaseHas('electronic_signatures', [
            'entity_name' => 'metal_detector_checks',
            'entity_id' => $check->id,
            'signature_purpose' => 'metal_detector_check',
        ]);
    }

    public function test_a_failing_check_is_audited_as_a_ccp_failure(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch();

        $check = app(RecordMetalDetectorCheckFeature::class)($batch, [
            'check_type' => MetalDetectorCheck::TYPE_HOURLY,
            'fe10_pass' => true,
            'non_fe15_pass' => true,
            'ss20_pass' => false,
            'failure_action' => 'Line stopped, QA notified',
        ], $user);

        $this->assertSame(MetalDetectorCheck::RESULT_FAIL, $check->overall_result);
        $this->assertDatabaseHas('audit_trails', [
            'entity_name' => 'metal_detector_checks',
            'action' => 'ccp_failure',
        ]);
    }

    public function test_a_pallecon_can_be_added_to_a_batch(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch();

        $pallecon = app(AddPalleconRecordFeature::class)($batch, [
            'serial_number' => 'PC-001',
            'fill_weight' => 800,
            'top_seal_number' => 'TS-1',
        ], $user);

        $this->assertSame('PC-001', $pallecon->serial_number);
        $this->assertSame($user->id, $pallecon->checked_by);
        $this->assertDatabaseHas('electronic_signatures', [
            'entity_name' => 'pallecon_records',
            'signature_purpose' => 'pallecon_checked',
        ]);
    }

    public function test_qa_can_approve_a_completed_batch(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch(BatchRecord::STATUS_COMPLETED);

        $approved = app(ApproveBatchQaFeature::class)($batch, $user);

        $this->assertSame(BatchRecord::STATUS_CLOSED, $approved->status);
        $this->assertDatabaseHas('electronic_signatures', [
            'entity_name' => 'batch_records',
            'signature_purpose' => 'qa_approval',
        ]);
    }

    public function test_qa_reject_returns_batch_to_production_with_reason(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch(BatchRecord::STATUS_COMPLETED);

        $rejected = app(RejectBatchQaFeature::class)($batch, $user, 'Weight out of spec');

        $this->assertSame(BatchRecord::STATUS_IN_PROGRESS, $rejected->status);
        $this->assertDatabaseHas('audit_trails', [
            'entity_name' => 'batch_records',
            'action' => 'reject',
            'reason' => 'Weight out of spec',
        ]);
    }

    public function test_qa_cannot_approve_a_batch_that_is_not_completed(): void
    {
        $user = User::factory()->create();
        $batch = $this->makeBatch();

        $this->expectException(BatchException::class);

        app(ApproveBatchQaFeature::class)($batch, $user);
    }
}
