<?php

namespace App\Features\Audit;

use App\Models\AuditTrail;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a filtered audit trail as a CSV download (scope §16 audit trail
 * report export).
 */
class ExportAuditTrailCsvFeature
{
    public function __construct(
        private readonly GenerateAuditTrailReportFeature $generate,
    ) {
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function __invoke(array $filters): StreamedResponse
    {
        $rows = ($this->generate)($filters);
        $filename = 'audit-trail-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['Timestamp', 'Entity', 'Entity ID', 'Field', 'Old', 'New', 'Action', 'Reason', 'User']);

            foreach ($rows as $row) {
                /** @var AuditTrail $row */
                fputcsv($handle, [
                    $row->created_at?->toDateTimeString(),
                    $row->entity_name,
                    $row->entity_id,
                    $row->field_name,
                    $row->old_value,
                    $row->new_value,
                    $row->action,
                    $row->reason,
                    $row->user?->name,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
