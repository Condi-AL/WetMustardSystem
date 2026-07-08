<?php

namespace App\Features\Drum;

use App\Domains\Drum\Jobs\AddDrumProcessingPalletJob;
use App\Domains\Signature\Jobs\RecordElectronicSignatureJob;
use App\Models\DrumProcessingPallet;
use App\Models\DrumProcessingRun;
use App\Models\User;

/**
 * Adds a pallet (pallecon-to-drum) to a drum processing run with a checked-by
 * electronic signature.
 */
class AddDrumPalletFeature
{
    public function __construct(
        private readonly AddDrumProcessingPalletJob $addDrumPallet,
        private readonly RecordElectronicSignatureJob $recordSignature,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(DrumProcessingRun $run, array $attributes, User $user): DrumProcessingPallet
    {
        $pallet = ($this->addDrumPallet)($run, $attributes, $user);

        ($this->recordSignature)($pallet, 'drum_pallet_checked', $user, 'Drum pallet checked');

        return $pallet;
    }
}
