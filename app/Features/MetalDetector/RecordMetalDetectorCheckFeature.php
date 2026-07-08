<?php

namespace App\Features\MetalDetector;

use App\Domains\Audit\Jobs\RecordAuditEntryJob;
use App\Domains\MetalDetector\Jobs\RecordMetalDetectorCheckJob;
use App\Domains\Signature\Jobs\RecordElectronicSignatureJob;
use App\Models\BatchRecord;
use App\Models\MetalDetectorCheck;
use App\Models\User;
use App\Operations\RaiseNotificationOperation;

/**
 * Records a metal detector verification check with an electronic signature and
 * audit entry. A failed check is audited as a CCP failure (scope §14.3
 * ccp_failure) and raises a real-time alert.
 */
class RecordMetalDetectorCheckFeature
{
    public function __construct(
        private readonly RecordMetalDetectorCheckJob $recordCheck,
        private readonly RecordElectronicSignatureJob $recordSignature,
        private readonly RecordAuditEntryJob $recordAuditEntry,
        private readonly RaiseNotificationOperation $raiseNotification,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(?BatchRecord $batch, array $attributes, User $user): MetalDetectorCheck
    {
        $check = ($this->recordCheck)($batch, $attributes, $user);

        ($this->recordSignature)(
            $check,
            'metal_detector_check',
            $user,
            "Metal detector {$check->check_type} check: {$check->overall_result}",
            $check->comments,
        );

        $action = $check->overall_result === MetalDetectorCheck::RESULT_FAIL ? 'ccp_failure' : 'create';
        ($this->recordAuditEntry)($check, $action, $user, 'overall_result', null, $check->overall_result);

        if ($check->overall_result === MetalDetectorCheck::RESULT_FAIL) {
            $message = $batch
                ? "Metal detector {$check->check_type} check FAILED on batch {$batch->batch_number}."
                : "Metal detector {$check->check_type} daily check FAILED.";

            ($this->raiseNotification)(
                'ccp_failure',
                $check,
                $message,
            );
        }

        return $check;
    }
}
