<?php

namespace App\Features\Reporting;

use App\Models\ReportSendLog;
use App\Models\User;
use App\Operations\SendReportOperation;
use Carbon\CarbonInterface;

/**
 * Manual "Send Now" of a report for a selected date range (scope §14.6).
 */
class SendReportNowFeature
{
    public function __construct(
        private readonly SendReportOperation $sendReport,
    ) {
    }

    public function __invoke(string $reportKey, CarbonInterface $from, CarbonInterface $to, ?User $user = null): ReportSendLog
    {
        return ($this->sendReport)($reportKey, $from, $to, 'manual', $user);
    }
}
