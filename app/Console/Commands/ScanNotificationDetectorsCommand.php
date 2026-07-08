<?php

namespace App\Console\Commands;

use App\Models\BatchRecord;
use App\Models\NotificationRule;
use App\Operations\RaiseNotificationOperation;
use Illuminate\Console\Command;

/**
 * Scans for time-based operational exceptions and raises notification events
 * (scope §14.3 detectors): missed metal detector checks, batches open too long
 * and overdue QA approvals. Intended to run periodically (scheduler/Task
 * Scheduler). Rule cooldowns prevent repeated alerts.
 */
class ScanNotificationDetectorsCommand extends Command
{
    protected $signature = 'dbmts:alerts:scan';

    protected $description = 'Detect operational exceptions and raise notification alerts.';

    public function handle(RaiseNotificationOperation $raise): int
    {
        $raised = 0;
        $raised += $this->missedMetalDetector($raise);
        $raised += $this->batchOpenTooLong($raise);
        $raised += $this->qaApprovalOverdue($raise);

        $this->info("Detector scan complete. {$raised} alert(s) raised.");

        return self::SUCCESS;
    }

    private function thresholdHours(string $ruleKey, int $default): int
    {
        $value = NotificationRule::query()->where('rule_key', $ruleKey)->value('trigger_condition');

        return is_numeric($value) ? (int) $value : $default;
    }

    private function missedMetalDetector(RaiseNotificationOperation $raise): int
    {
        $threshold = now()->subHours($this->thresholdHours('missed_metal_detector_check', 2));
        $count = 0;

        BatchRecord::query()
            ->with('metalDetectorChecks')
            ->where('status', BatchRecord::STATUS_IN_PROGRESS)
            ->get()
            ->each(function (BatchRecord $batch) use ($raise, $threshold, &$count): void {
                $last = $batch->metalDetectorChecks->max('check_time');
                if ($last === null || $last->lt($threshold)) {
                    $event = $raise('missed_metal_detector_check', $batch, "Batch {$batch->batch_number} has an overdue metal detector check.");
                    $count += $event ? 1 : 0;
                }
            });

        return $count;
    }

    private function batchOpenTooLong(RaiseNotificationOperation $raise): int
    {
        $hours = $this->thresholdHours('batch_open_too_long', 24);
        $count = 0;

        BatchRecord::query()
            ->where('status', BatchRecord::STATUS_IN_PROGRESS)
            ->where('created_at', '<', now()->subHours($hours))
            ->get()
            ->each(function (BatchRecord $batch) use ($raise, $hours, &$count): void {
                $event = $raise('batch_open_too_long', $batch, "Batch {$batch->batch_number} has been open longer than {$hours}h.");
                $count += $event ? 1 : 0;
            });

        return $count;
    }

    private function qaApprovalOverdue(RaiseNotificationOperation $raise): int
    {
        $hours = $this->thresholdHours('qa_approval_overdue', 4);
        $count = 0;

        BatchRecord::query()
            ->where('status', BatchRecord::STATUS_COMPLETED)
            ->whereNotNull('completed_at')
            ->where('completed_at', '<', now()->subHours($hours))
            ->get()
            ->each(function (BatchRecord $batch) use ($raise, $hours, &$count): void {
                $event = $raise('qa_approval_overdue', $batch, "Batch {$batch->batch_number} has awaited QA approval for over {$hours}h.");
                $count += $event ? 1 : 0;
            });

        return $count;
    }
}
