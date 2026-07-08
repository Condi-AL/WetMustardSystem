<?php

namespace App\Domains\Notification\Jobs;

use App\Models\NotificationEvent;
use App\Models\NotificationRule;
use Illuminate\Database\Eloquent\Model;

/**
 * Creates a notification event for a rule, honouring the rule's enabled flag and
 * cooldown window (scope §14.3). Returns null when the alert is suppressed
 * (rule missing/disabled, or an event for the same rule+entity was raised within
 * the cooldown period).
 */
class RaiseNotificationEventJob
{
    public function __invoke(string $ruleKey, ?Model $entity, string $message, ?string $severity = null): ?NotificationEvent
    {
        $rule = NotificationRule::query()->where('rule_key', $ruleKey)->first();

        if ($rule === null || ! $rule->enabled) {
            return null;
        }

        $entityName = $entity?->getTable();
        $entityId = $entity?->getKey();

        if ($this->withinCooldown($rule, $entityName, $entityId)) {
            return null;
        }

        return NotificationEvent::create([
            'rule_key' => $ruleKey,
            'entity_name' => $entityName,
            'entity_id' => $entityId,
            'severity' => $severity ?? $rule->severity,
            'message' => $message,
            'status' => NotificationEvent::STATUS_OPEN,
            'triggered_at' => now(),
        ]);
    }

    private function withinCooldown(NotificationRule $rule, ?string $entityName, int|string|null $entityId): bool
    {
        if ($rule->cooldown_minutes <= 0) {
            return false;
        }

        return NotificationEvent::query()
            ->where('rule_key', $rule->rule_key)
            ->where('entity_name', $entityName)
            ->where('entity_id', $entityId)
            ->where('triggered_at', '>=', now()->subMinutes($rule->cooldown_minutes))
            ->exists();
    }
}
