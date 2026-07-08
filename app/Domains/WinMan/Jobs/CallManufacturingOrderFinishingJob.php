<?php

namespace App\Domains\WinMan\Jobs;

use App\Domains\WinMan\Support\WinManConnection;
use PDO;

/**
 * Executes the approved WinMan finishing stored procedure
 * (wsp_ManufacturingOrdersFinishing) to book completed finished goods against an
 * existing MO (scope §11.5). This is the ONLY sanctioned write path to WinMan;
 * DBMTS never issues direct INSERT/UPDATE statements.
 *
 * Uses the full finishing pattern with the internal ManufacturingOrder BIGINT,
 * quantity to complete, finished/expiry dates, internal Location key, lot/batch
 * number, output completed Inventory ID and LastModifiedDate concurrency value.
 * Stored-procedure result sets are fully drained before the output parameters
 * are read.
 *
 * @return array{completed_inventory: ?int, last_modified_date: ?string}
 */
class CallManufacturingOrderFinishingJob
{
    public function __construct(
        private readonly WinManConnection $winman,
    ) {
    }

    /**
     * @param  array{manufacturing_order: int, quantity_to_complete: float, finished_date: string, expiry_date: string, location: int, lot_number: string, user_name: string, notes: string, last_modified_date: string}  $params
     * @return array{completed_inventory: ?int, last_modified_date: ?string}
     */
    public function __invoke(array $params): array
    {
        $procedure = (string) config('winman.booking.procedure', 'wsp_ManufacturingOrdersFinishing');

        /** @var \Illuminate\Database\Connection $connection */
        $connection = $this->winman->connection();
        $pdo = $connection->getPdo();

        $statement = $pdo->prepare("{CALL {$procedure}(?,?,?,?,?,?,?,?,?,?,?,?,?)}");

        $completedInventory = null;
        $lastModifiedDate = $params['last_modified_date'];

        $statement->bindValue(1, $params['manufacturing_order'], PDO::PARAM_INT);
        $statement->bindValue(2, $params['quantity_to_complete']);
        $statement->bindValue(3, 0);
        $statement->bindValue(4, $params['finished_date']);
        $statement->bindValue(5, $params['expiry_date']);
        $statement->bindValue(6, $params['location'], PDO::PARAM_INT);
        $statement->bindValue(7, $params['lot_number']);
        $statement->bindValue(8, $params['notes']);
        $statement->bindValue(9, $params['user_name']);
        $statement->bindValue(10, null, PDO::PARAM_NULL);
        $statement->bindValue(11, null, PDO::PARAM_NULL);
        $statement->bindParam(12, $completedInventory, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 20);
        $statement->bindParam(13, $lastModifiedDate, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 30);

        $statement->execute();

        // Fully drain any result sets before reading output parameters.
        do {
            // no-op; discard rows
        } while ($this->advanceRowset($statement));

        return [
            'completed_inventory' => $completedInventory !== null ? (int) $completedInventory : null,
            'last_modified_date' => $lastModifiedDate !== null ? (string) $lastModifiedDate : null,
        ];
    }

    private function advanceRowset(\PDOStatement $statement): bool
    {
        try {
            return $statement->nextRowset();
        } catch (\PDOException) {
            return false;
        }
    }
}
