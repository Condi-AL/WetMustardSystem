<?php

namespace App\Domains\Audit\Jobs;

use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Records an audit trail entry for a state change or correction.
 *
 * Corrections should always supply old value, new value and a reason (scope §11
 * validation). entity_name is derived from the model's table.
 */
class RecordAuditEntryJob
{
    public function __invoke(
        Model $entity,
        string $action,
        ?User $user = null,
        ?string $field = null,
        int|float|string|null $oldValue = null,
        int|float|string|null $newValue = null,
        ?string $reason = null,
    ): AuditTrail {
        return AuditTrail::create([
            'entity_name' => $entity->getTable(),
            'entity_id' => $entity->getKey(),
            'field_name' => $field,
            'old_value' => $oldValue !== null ? (string) $oldValue : null,
            'new_value' => $newValue !== null ? (string) $newValue : null,
            'action' => $action,
            'reason' => $reason,
            'user_id' => $user?->id,
        ]);
    }
}
