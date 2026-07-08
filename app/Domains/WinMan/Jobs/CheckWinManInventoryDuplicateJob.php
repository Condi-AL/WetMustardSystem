<?php

namespace App\Domains\WinMan\Jobs;

use App\Domains\WinMan\Support\WinManConnection;

/**
 * Checks the WinMan Inventory table for existing lot/batch numbers before
 * booking (scope §11.6). Matches Inventory.LotNumber via LIKE for each supplied
 * lot number (mirrors the existing production check). Read-only.
 *
 * @return array<int, array{lot: string, matched: string, product: ?string}>
 */
class CheckWinManInventoryDuplicateJob
{
    public function __construct(
        private readonly WinManConnection $winman,
    ) {
    }

    /**
     * @param  array<int, string>  $lotNumbers
     * @return array<int, array{lot: string, matched: string, product: ?string}>
     */
    public function __invoke(array $lotNumbers): array
    {
        $connection = $this->winman->connection();
        $found = [];

        foreach ($lotNumbers as $lot) {
            $lot = trim((string) $lot);
            if ($lot === '') {
                continue;
            }

            $rows = $connection->select(
                'SELECT LotNumber, Product, Location FROM Inventory WHERE LotNumber LIKE ?',
                ['%'.$lot.'%'],
            );

            foreach ($rows as $row) {
                $found[] = [
                    'lot' => $lot,
                    'matched' => (string) $row->LotNumber,
                    'product' => $row->Product !== null ? (string) $row->Product : null,
                ];
            }
        }

        return $found;
    }
}
