<?php

namespace App\Domains\Packing\Jobs;

use App\Models\PackingRun;
use App\Models\PackingWeightCheck;
use App\Models\User;

/**
 * Records a packing weight check. The average of the supplied sample weights is
 * derived; the pass/fail result is taken from the supplied value.
 */
class RecordPackingWeightCheckJob
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(PackingRun $run, array $attributes, User $user): PackingWeightCheck
    {
        $weights = [];
        for ($i = 1; $i <= 6; $i++) {
            $value = $attributes["weight_{$i}"] ?? null;
            if ($value !== null && $value !== '') {
                $weights[$i] = (float) $value;
            }
        }

        $average = $weights !== [] ? round(array_sum($weights) / count($weights), 3) : null;

        return $run->weightChecks()->create([
            'check_time' => now(),
            'weight_1' => $weights[1] ?? null,
            'weight_2' => $weights[2] ?? null,
            'weight_3' => $weights[3] ?? null,
            'weight_4' => $weights[4] ?? null,
            'weight_5' => $weights[5] ?? null,
            'weight_6' => $weights[6] ?? null,
            'average_weight' => $average,
            'result' => ($attributes['result'] ?? PackingWeightCheck::RESULT_PASS) === PackingWeightCheck::RESULT_FAIL
                ? PackingWeightCheck::RESULT_FAIL
                : PackingWeightCheck::RESULT_PASS,
            'signed_by' => $user->id,
            'signed_at' => now(),
        ]);
    }
}
