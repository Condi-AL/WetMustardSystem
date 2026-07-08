<?php

namespace App\Features\Audit;

use App\Models\AuditTrail;
use Illuminate\Support\Collection;

/**
 * Returns filtered audit trail entries for the audit report (scope §16 - audit
 * trail report by record, date range or user).
 */
class GenerateAuditTrailReportFeature
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, AuditTrail>
     */
    public function __invoke(array $filters): Collection
    {
        return AuditTrail::query()
            ->with('user')
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($filters['entity_name'] ?? null, fn ($q, $v) => $q->where('entity_name', $v))
            ->when($filters['action'] ?? null, fn ($q, $v) => $q->where('action', $v))
            ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->latest('id')
            ->limit(500)
            ->get();
    }
}
