<?php

namespace App\Domains\Packing\Jobs;

use App\Models\PackingHourlyCheck;
use App\Models\PackingRun;
use App\Models\User;

/**
 * Records an hourly hygiene/quality check for a packing run.
 */
class RecordPackingHourlyCheckJob
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(PackingRun $run, array $attributes, User $user): PackingHourlyCheck
    {
        return $run->hourlyChecks()->create([
            'check_time' => now(),
            'bucket_clean' => (bool) ($attributes['bucket_clean'] ?? false),
            'lid_clean' => (bool) ($attributes['lid_clean'] ?? false),
            'lids_secure' => (bool) ($attributes['lids_secure'] ?? false),
            'tamper_in_place' => (bool) ($attributes['tamper_in_place'] ?? false),
            'label_correct' => (bool) ($attributes['label_correct'] ?? false),
            'print_clear' => (bool) ($attributes['print_clear'] ?? false),
            'lot_code_correct' => (bool) ($attributes['lot_code_correct'] ?? false),
            'filler_clean' => (bool) ($attributes['filler_clean'] ?? false),
            'fill_clean' => (bool) ($attributes['fill_clean'] ?? false),
            'signed_by' => $user->id,
            'signed_at' => now(),
        ]);
    }
}
