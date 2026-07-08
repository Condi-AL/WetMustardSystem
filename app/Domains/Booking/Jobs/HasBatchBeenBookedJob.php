<?php

namespace App\Domains\Booking\Jobs;

use App\Models\BatchRecord;
use App\Models\WinManBookingLog;

/**
 * Returns whether a batch has already been successfully booked to WinMan,
 * preventing a duplicate booking (scope §11.6).
 */
class HasBatchBeenBookedJob
{
    public function __invoke(BatchRecord $batch): bool
    {
        return WinManBookingLog::query()
            ->where('batch_record_id', $batch->id)
            ->where('booking_status', WinManBookingLog::STATUS_SUCCESS)
            ->exists();
    }
}
