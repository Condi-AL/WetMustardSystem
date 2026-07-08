<?php

namespace App\Features\Packing;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\Packing\Jobs\RecordPackingWeightCheckJob;
use App\Domains\Signature\Jobs\RecordElectronicSignatureJob;
use App\Models\PackingRun;
use App\Models\PackingWeightCheck;
use App\Models\User;
use App\Operations\RaiseNotificationOperation;

/**
 * Records a packing weight check with an electronic signature. A failing check
 * is audited as a packing weight breach (scope §14.3 packing_weight_breach) and
 * raises a real-time alert.
 */
class RecordPackingWeightCheckFeature
{
    public function __construct(
        private readonly RecordPackingWeightCheckJob $recordCheck,
        private readonly RecordElectronicSignatureJob $recordSignature,
        private readonly RecordAuditEntryJob $recordAuditEntry,
        private readonly RaiseNotificationOperation $raiseNotification,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(PackingRun $run, array $attributes, User $user): PackingWeightCheck
    {
        $check = ($this->recordCheck)($run, $attributes, $user);

        ($this->recordSignature)($check, 'packing_weight_check', $user, "Packing weight check: {$check->result}");

        if ($check->result === PackingWeightCheck::RESULT_FAIL) {
            ($this->recordAuditEntry)($check, 'packing_weight_breach', $user, 'result', null, $check->result);
            ($this->raiseNotification)(
                'packing_weight_breach',
                $check,
                "Packing weight check FAILED (average {$check->average_weight}).",
            );
        }

        return $check;
    }
}
