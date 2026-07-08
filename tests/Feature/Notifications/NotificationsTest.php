<?php

namespace Tests\Feature\Notifications;

use App\Features\MetalDetector\RecordMetalDetectorCheckFeature;
use App\Models\BatchRecord;
use App\Models\ManufacturingOrder;
use App\Models\MetalDetectorCheck;
use App\Models\NotificationEvent;
use App\Models\NotificationRecipient;
use App\Models\NotificationRule;
use App\Models\Product;
use App\Models\User;
use App\Operations\RaiseNotificationOperation;
use App\Operations\SendOffice365MailOperation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function rule(string $key, int $cooldown = 0, string $type = 'event', ?string $condition = null): NotificationRule
    {
        return NotificationRule::create([
            'rule_key' => $key, 'rule_name' => $key, 'event_type' => $type,
            'severity' => 'warning', 'enabled' => true, 'cooldown_minutes' => $cooldown, 'trigger_condition' => $condition,
        ]);
    }

    private function makeBatch(string $status = BatchRecord::STATUS_IN_PROGRESS): BatchRecord
    {
        $product = Product::create(['recipe_code' => 'R1', 'product_name' => 'Test', 'active_flag' => true]);
        $order = ManufacturingOrder::create([
            'mo_number' => 'MO1', 'winman_manufacturing_order' => 1, 'winman_manufacturing_order_id' => 'MO1',
            'recipe_code' => 'R1', 'product_id' => $product->id, 'planned_quantity' => 1, 'quantity_outstanding' => 1,
            'winman_system_type' => 'F', 'status' => 'selected',
        ]);

        return BatchRecord::create([
            'manufacturing_order_id' => $order->id, 'product_id' => $product->id, 'batch_number' => 'WM-N1',
            'production_date' => now()->toDateString(), 'status' => $status,
        ]);
    }

    public function test_a_failed_metal_detector_check_raises_a_ccp_failure_event(): void
    {
        $this->rule('ccp_failure');
        $user = User::factory()->create();
        $batch = $this->makeBatch();

        app(RecordMetalDetectorCheckFeature::class)($batch, [
            'check_type' => MetalDetectorCheck::TYPE_HOURLY,
            'fe10_pass' => true, 'non_fe15_pass' => true, 'ss20_pass' => false,
            'failure_action' => 'Line stopped',
        ], $user);

        $this->assertDatabaseHas('notification_events', [
            'rule_key' => 'ccp_failure',
            'entity_name' => 'metal_detector_checks',
        ]);
    }

    public function test_cooldown_suppresses_repeat_events_for_the_same_entity(): void
    {
        $this->rule('ccp_failure', cooldown: 60);
        $batch = $this->makeBatch();

        $first = app(RaiseNotificationOperation::class)('ccp_failure', $batch, 'first');
        $second = app(RaiseNotificationOperation::class)('ccp_failure', $batch, 'second');

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertSame(1, NotificationEvent::count());
    }

    public function test_disabled_rule_raises_nothing(): void
    {
        $rule = $this->rule('ccp_failure');
        $rule->update(['enabled' => false]);
        $batch = $this->makeBatch();

        $this->assertNull(app(RaiseNotificationOperation::class)('ccp_failure', $batch, 'x'));
        $this->assertSame(0, NotificationEvent::count());
    }

    public function test_alert_with_recipients_sends_and_writes_a_send_log(): void
    {
        $mail = Mockery::mock(SendOffice365MailOperation::class);
        $mail->shouldReceive('__invoke')->once();
        $this->instance(SendOffice365MailOperation::class, $mail);

        $this->rule('ccp_failure');
        NotificationRecipient::create(['rule_key' => null, 'recipient_type' => 'direct', 'recipient_email' => 'qa@example.com', 'enabled' => true]);
        $batch = $this->makeBatch();

        app(RaiseNotificationOperation::class)('ccp_failure', $batch, 'CCP failed');

        $this->assertDatabaseHas('report_send_logs', ['report_key' => 'ccp_failure', 'trigger_mode' => 'alert', 'status' => 'sent']);
    }

    public function test_detector_scan_raises_missed_metal_detector_alerts(): void
    {
        $this->rule('missed_metal_detector_check', cooldown: 0, type: 'detector', condition: '2');
        $this->makeBatch();

        Artisan::call('dbmts:alerts:scan');

        $this->assertDatabaseHas('notification_events', ['rule_key' => 'missed_metal_detector_check']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
