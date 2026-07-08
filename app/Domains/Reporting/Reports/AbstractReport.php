<?php

namespace App\Domains\Reporting\Reports;

use App\Domains\Reporting\Contracts\ReportGenerator;
use Carbon\CarbonInterface;
use Illuminate\Support\HtmlString;

/**
 * Base report: renders a self-contained, inline-styled HTML table suitable for
 * email clients. Concrete reports implement key(), name() and data().
 */
abstract class AbstractReport implements ReportGenerator
{
    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, string|int|float|null>>, summary?: string}
     */
    abstract protected function data(CarbonInterface $from, CarbonInterface $to): array;

    public function generate(CarbonInterface $from, CarbonInterface $to): array
    {
        $data = $this->data($from, $to);
        $rows = $data['rows'];

        return [
            'subject' => sprintf('DBMTS · %s (%s to %s)', $this->name(), $from->toDateString(), $to->toDateString()),
            'html' => $this->render($from, $to, $data['headers'], $rows, $data['summary'] ?? null),
            'row_count' => count($rows),
        ];
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, string|int|float|null>>  $rows
     */
    protected function render(CarbonInterface $from, CarbonInterface $to, array $headers, array $rows, ?string $summary): string
    {
        $th = collect($headers)->map(fn ($h) => '<th style="text-align:left;padding:8px;border-bottom:2px solid #ddd;font-size:12px;color:#555;">'.e($h).'</th>')->implode('');

        $tr = collect($rows)->map(function (array $row): string {
            $cells = collect($row)->map(fn ($c) => '<td style="padding:8px;border-bottom:1px solid #eee;font-size:13px;">'.e((string) $c).'</td>')->implode('');

            return "<tr>{$cells}</tr>";
        })->implode('');

        if ($rows === []) {
            $tr = '<tr><td colspan="'.count($headers).'" style="padding:16px;text-align:center;color:#999;">No records for this period.</td></tr>';
        }

        $summaryHtml = $summary !== null ? '<p style="color:#555;font-size:13px;">'.e($summary).'</p>' : '';

        return new HtmlString(<<<HTML
            <div style="font-family:Arial,Helvetica,sans-serif;max-width:800px;">
                <h2 style="color:#1f2937;">{$this->name()}</h2>
                <p style="color:#6b7280;font-size:13px;">Period: {$from->toDateString()} to {$to->toDateString()}</p>
                {$summaryHtml}
                <table style="border-collapse:collapse;width:100%;">
                    <thead><tr>{$th}</tr></thead>
                    <tbody>{$tr}</tbody>
                </table>
            </div>
            HTML);
    }
}
