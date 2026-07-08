<?php

namespace App\Domains\Booking\Jobs;

use App\Models\WinManBookingLog;

/**
 * Persists a WinMan booking attempt outcome to the local booking log (scope
 * §11.6): every attempted and successful booking is recorded.
 */
class RecordWinManBookingLogJob
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes): WinManBookingLog
    {
        return WinManBookingLog::create(array_merge(['booking_date' => now()], $attributes));
    }
}
