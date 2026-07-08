<?php

namespace App\Domains\Reporting\Jobs;

use App\Models\ReportRecipient;
use App\Models\User;

/**
 * Resolves the To/Cc recipient email lists for a report from global, per-report
 * and role-based recipients (scope §14.4).
 *
 * @return array{to: array<int, string>, cc: array<int, string>}
 */
class ResolveReportRecipientsJob
{
    /**
     * @return array{to: array<int, string>, cc: array<int, string>}
     */
    public function __invoke(string $reportKey): array
    {
        $recipients = ReportRecipient::query()
            ->where('enabled', true)
            ->where(function ($query) use ($reportKey): void {
                $query->whereNull('report_key')->orWhere('report_key', $reportKey);
            })
            ->get();

        $to = [];
        $cc = [];

        foreach ($recipients as $recipient) {
            $emails = $recipient->recipient_type === ReportRecipient::TYPE_ROLE
                ? $this->emailsForRole($recipient->role_key)
                : array_filter([$recipient->recipient_email]);

            foreach ($emails as $email) {
                if ($recipient->is_cc) {
                    $cc[] = $email;
                } else {
                    $to[] = $email;
                }
            }
        }

        return [
            'to' => array_values(array_unique(array_filter($to))),
            'cc' => array_values(array_unique(array_filter($cc))),
        ];
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
