<?php

namespace App\Features\Batches;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Batch\Jobs\RecordProcessParameterJob;
use App\Models\BatchProcessParameter;
use App\Models\BatchProcessStep;
use App\Models\BatchRecord;
use App\Models\User;

/**
 * Records a process parameter value against a batch and audits it.
 */
class RecordProcessParameterFeature
{
    public function __construct(
        private readonly RecordProcessParameterJob $recordProcessParameter,
        private readonly RecordAuditEntryJob $recordAuditEntry,
    ) {
    }

    public function __invoke(
        BatchRecord $batch,
        string $name,
        ?string $value,
        ?string $uom,
        User $user,
        ?BatchProcessStep $step = null,
    ): BatchProcessParameter {
        $parameter = ($this->recordProcessParameter)($batch, $name, $value, $uom, $user, $step);

        ($this->recordAuditEntry)($parameter, 'create', $user, $name, null, $value);

        return $parameter;
    }
}
