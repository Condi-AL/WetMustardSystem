<?php

namespace App\Domains\WinMan\Jobs;

use App\Domains\WinMan\Support\WinManConnection;

/**
 * Looks up WinMan Products.PackSize for a product code (scope §11.4).
 *
 * Used for converting booking quantities into traded units. Read-only.
 */
class GetWinManProductPackSizeJob
{
    public function __construct(
        private readonly WinManConnection $winman,
    ) {
    }

    public function __invoke(string $winmanProductId): ?float
    {
        $row = $this->winman->connection()->selectOne(
            'SELECT PackSize FROM Products WHERE ProductId = ?',
            [$winmanProductId],
        );

        if ($row === null || $row->PackSize === null) {
            return null;
        }

        return (float) $row->PackSize;
    }
}
