<?php

namespace App\Features\Packing;

use App\Domains\Packing\Jobs\RecordPackingHourlyCheckJob;
use App\Domains\Signature\Jobs\RecordElectronicSignatureJob;
use App\Models\PackingHourlyCheck;
use App\Models\PackingRun;
use App\Models\User;

/**
 * Records a packing hourly hygiene check with an electronic signature.
 */
class RecordPackingHourlyCheckFeature
{
    public function __construct(
        private readonly RecordPackingHourlyCheckJob $recordCheck,
        private readonly RecordElectronicSignatureJob $recordSignature,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(PackingRun $run, array $attributes, User $user): PackingHourlyCheck
    {
        $check = ($this->recordCheck)($run, $attributes, $user);

        ($this->recordSignature)($check, 'packing_hourly_check', $user, 'Packing hourly hygiene check completed');

        return $check;
    }
}
