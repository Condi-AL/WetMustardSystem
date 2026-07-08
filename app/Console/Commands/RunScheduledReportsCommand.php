<?php

namespace App\Console\Commands;

use App\Domains\Reporting\ReportRegistry;
use App\Models\ReportConfig;
use App\Operations\SendReportOperation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Runs enabled scheduled DBMTS reports for a date, using each report's date
 * offsets. Intended to be triggered by the framework scheduler or Windows Task
 * Scheduler (scope §14.5).
 */
class RunScheduledReportsCommand extends Command
{
    protected $signature = 'dbmts:reports:run
        {--date= : Base date (defaults to today)}
        {--key= : Only run a single report key}';

    protected $description = 'Generate and send enabled scheduled DBMTS reports.';

    public function handle(ReportRegistry $registry, SendReportOperation $sendReport): int
    {
        $baseDate = $this->option('date') ? Carbon::parse($this->option('date')) : now();

        $query = ReportConfig::query()->where('enabled', true);
        if ($key = $this->option('key')) {
            $query->where('report_key', $key);
        }

        $configs = $query->get();

        if ($configs->isEmpty()) {
            $this->warn('No enabled reports to run.');

            return self::SUCCESS;
        }

        foreach ($configs as $config) {
            if (! $registry->has($config->report_key)) {
                $this->warn("Skipping unregistered report: {$config->report_key}");

                continue;
            }

            $from = $baseDate->copy()->addDays($config->date_offset_from_days);
            $to = $baseDate->copy()->addDays($config->date_offset_to_days);

            $log = $sendReport($config->report_key, $from, $to, 'scheduled');

            $this->line(sprintf('%-32s %s (rows: %s)', $config->report_key, $log->status, $log->row_count ?? 0));
        }

        return self::SUCCESS;
    }
}
