<?php

namespace App\Domains\Booking\Jobs;

use App\Models\WinManIssueLog;

class RecordWinManIssueLogJob
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes): WinManIssueLog
    {
        return WinManIssueLog::create(array_merge(['issue_date' => now()], $attributes));
    }
}
