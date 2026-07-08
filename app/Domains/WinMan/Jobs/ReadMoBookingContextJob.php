<?php

namespace App\Domains\WinMan\Jobs;

use App\Domains\WinMan\Support\WinManConnection;

/**
 * Reads the pre-booking context for a WinMan MO immediately before booking
 * (scope §11.5): the LastModifiedDate concurrency value, outstanding quantity,
 * order quantity and internal Location. Read-only.
 *
 * @return array{last_modified_date: string, quantity_outstanding: float, quantity: float, location: int}|null
 */
class ReadMoBookingContextJob
{
    public function __construct(
        private readonly WinManConnection $winman,
    ) {
    }

    /**
     * @return array{last_modified_date: string, quantity_outstanding: float, quantity: float, location: int}|null
     */
    public function __invoke(int $winmanManufacturingOrder): ?array
    {
        $row = $this->winman->connection()->selectOne(
            'SELECT LastModifiedDate, QuantityOutstanding, Quantity, Location
             FROM ManufacturingOrders WHERE ManufacturingOrder = ?',
            [$winmanManufacturingOrder],
        );

        if ($row === null) {
            return null;
        }

        return [
            'last_modified_date' => (string) $row->LastModifiedDate,
            'quantity_outstanding' => (float) $row->QuantityOutstanding,
            'quantity' => (float) $row->Quantity,
            'location' => (int) $row->Location,
        ];
    }
}
