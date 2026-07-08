<?php

namespace App\Operations;

use App\Domains\Notification\Jobs\RaiseNotificationEventJob;
use App\Domains\Notification\Jobs\ResolveNotificationRecipientsJob;
use App\Models\NotificationEvent;
use App\Models\ReportSendLog;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Raises a notification event (honouring rule enablement + cooldown) and, when
 * recipients exist, sends an alert email and writes an alert send-log row
 * (scope §11 - every alert-driven send must create a send-log entry).
 *
 * Reused by immediate event triggers (CCP failure, weight breach) and by the
 * scheduled detector command.
 */
class RaiseNotificationOperation
{
    public function __construct(
        private readonly RaiseNotificationEventJob $raiseEvent,
        private readonly ResolveNotificationRecipientsJob $resolveRecipients,
        private readonly SendOffice365MailOperation $sendMail,
    ) {
    }

    public function __invoke(string $ruleKey, ?Model $entity, string $message, ?string $severity = null): ?NotificationEvent
    {
        $event = ($this->raiseEvent)($ruleKey, $entity, $message, $severity);

        if ($event === null) {
            return null;
        }

        $recipients = ($this->resolveRecipients)($ruleKey);

        if ($recipients === []) {
            return $event;
        }

        $log = ReportSendLog::create([
            'report_key' => $ruleKey,
            'trigger_mode' => 'alert',
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
            'recipients_to' => implode(', ', $recipients),
            'status' => ReportSendLog::STATUS_RUNNING,
            'row_count' => 1,
            'started_at' => now(),
        ]);

        try {
            ($this->sendMail)(
                $recipients,
                "[DBMTS {$event->severity}] {$event->rule_key}",
                $this->html($event),
            );
            $log->status = ReportSendLog::STATUS_SENT;
        } catch (Throwable $e) {
            $log->status = ReportSendLog::STATUS_FAILED;
            $log->error_message = $e->getMessage();
        } finally {
            $log->completed_at = now();
            $log->save();
        }

        return $event;
    }

    private function html(NotificationEvent $event): string
    {
        return sprintf(
            '<div style="font-family:Arial,sans-serif;"><h3 style="color:#b91c1c;">DBMTS Alert: %s</h3><p><strong>Severity:</strong> %s</p><p>%s</p><p style="color:#6b7280;font-size:12px;">Triggered %s</p></div>',
            e($event->rule_key),
            e($event->severity),
            e($event->message),
            $event->triggered_at?->toDateTimeString(),
        );
    }
}
