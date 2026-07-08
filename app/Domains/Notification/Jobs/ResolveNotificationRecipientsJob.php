<?php

namespace App\Domains\Notification\Jobs;

use App\Models\NotificationRecipient;
use App\Models\User;

/**
 * Resolves alert recipient emails for a rule from global (rule_key NULL),
 * per-rule and role-based recipients (scope §14.4).
 *
 * @return array<int, string>
 */
class ResolveNotificationRecipientsJob
{
    /**
     * @return array<int, string>
     */
    public function __invoke(string $ruleKey): array
    {
        $recipients = NotificationRecipient::query()
            ->where('enabled', true)
            ->where(function ($query) use ($ruleKey): void {
                $query->whereNull('rule_key')->orWhere('rule_key', $ruleKey);
            })
            ->get();

        $emails = [];
        foreach ($recipients as $recipient) {
            if ($recipient->recipient_type === NotificationRecipient::TYPE_ROLE) {
                $emails = array_merge($emails, $this->emailsForRole($recipient->role_key));
            } elseif ($recipient->recipient_email !== null) {
                $emails[] = $recipient->recipient_email;
            }
        }

        return array_values(array_unique(array_filter($emails)));
    }

    /**
     * @return array<int, string>
     */
    private function emailsForRole(?string $roleKey): array
    {
        if ($roleKey === null || $roleKey === '') {
            return [];
        }

        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', $roleKey))
            ->pluck('email')
            ->all();
    }
}
