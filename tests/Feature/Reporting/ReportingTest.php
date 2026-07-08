<?php

namespace Tests\Feature\Reporting;

use App\Domains\Reporting\Jobs\ResolveReportRecipientsJob;
use App\Domains\Reporting\Reports\OpenBatchesReport;
use App\Features\Reporting\SendReportNowFeature;
use App\Models\BatchRecord;
use App\Models\ManufacturingOrder;
use App\Models\Product;
use App\Models\ReportRecipient;
use App\Models\ReportSendLog;
use App\Models\User;
use App\Operations\SendOffice365MailOperation;
use App\Operations\SendReportOperation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportingTest extends TestCase
{
    use RefreshDatabase;

    private function fakeMailer(bool $expectSend = true): void
    {
        $mail = Mockery::mock(SendOffice365MailOperation::class);
        if ($expectSend) {
            $mail->shouldReceive('__invoke')->once();
        } else {
            $mail->shouldReceive('__invoke')->never();
        }
        $this->instance(SendOffice365MailOperation::class, $mail);
    }

    public function test_open_batches_report_generates_html_with_row_count(): void
    {
        $product = Product::create(['recipe_code' => 'R1', 'product_name' => 'Test', 'active_flag' => true]);
        $order = ManufacturingOrder::create([
            'mo_number' => 'MO1', 'winman_manufacturing_order' => 1, 'winman_manufacturing_order_id' => 'MO1',
            'recipe_code' => 'R1', 'product_id' => $product->id, 'planned_quantity' => 1, 'quantity_outstanding' => 1,
            'winman_system_type' => 'F', 'status' => 'selected',
        ]);
        BatchRecord::create([
            'manufacturing_order_id' => $order->id, 'product_id' => $product->id, 'batch_number' => 'WM-1',
            'production_date' => now()->toDateString(), 'status' => BatchRecord::STATUS_IN_PROGRESS,
        ]);

        $report = app(OpenBatchesReport::class)->generate(now()->subDay(), now());

        $this->assertSame(1, $report['row_count']);
        $this->assertStringContainsString('WM-1', $report['html']);
    }

    public function test_send_report_now_sends_and_logs_when_recipients_exist(): void
    {
        $this->fakeMailer();
        ReportRecipient::create(['report_key' => null, 'recipient_type' => 'direct', 'recipient_email' => 'qa@example.com', 'is_cc' => false, 'enabled' => true]);

        $log = app(SendReportNowFeature::class)(OpenBatchesReport::KEY, now()->subDay(), now(), null);

        $this->assertSame(ReportSendLog::STATUS_SENT, $log->status);
        $this->assertStringContainsString('qa@example.com', $log->recipients_to);
    }

    public function test_send_is_skipped_when_no_recipients(): void
    {
        $this->fakeMailer(expectSend: false);

        $log = app(SendReportOperation::class)(OpenBatchesReport::KEY, now()->subDay(), now(), 'manual');

        $this->assertSame(ReportSendLog::STATUS_SKIPPED, $log->status);
    }

    public function test_unknown_report_key_is_logged_as_failed(): void
    {
        $this->fakeMailer(expectSend: false);

        $log = app(SendReportOperation::class)('not_a_real_report', now()->subDay(), now(), 'manual');

        $this->assertSame(ReportSendLog::STATUS_FAILED, $log->status);
        $this->assertStringContainsString('not registered', (string) $log->error_message);
    }

    public function test_role_recipients_resolve_to_user_emails(): void
    {
        Role::create(['name' => 'qa_technical', 'guard_name' => 'web']);
        $user = User::factory()->create(['email' => 'tech@example.com']);
        $user->assignRole('qa_technical');

        ReportRecipient::create(['report_key' => null, 'recipient_type' => 'role', 'role_key' => 'qa_technical', 'is_cc' => false, 'enabled' => true]);

        $resolved = app(ResolveReportRecipientsJob::class)(OpenBatchesReport::KEY);

        $this->assertContains('tech@example.com', $resolved['to']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
