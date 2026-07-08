<?php

namespace App\Console\Commands;

use App\Domains\WinMan\Data\ComponentData;
use App\Domains\WinMan\Data\ManufacturingOrderData;
use App\Domains\WinMan\Jobs\FetchManufacturingOrderComponentsJob;
use App\Domains\WinMan\Jobs\SearchOutstandingManufacturingOrdersJob;
use App\Domains\WinMan\Support\WinManConnection;
use App\Features\ManufacturingOrders\SelectManufacturingOrderFeature;
use Illuminate\Console\Command;
use Throwable;

/**
 * Diagnostics for the WinMan read integration (scope §15 WinMan Integration
 * Admin: environment status, MO fetch diagnostics, component review).
 */
class WinManDiagnosticsCommand extends Command
{
    protected $signature = 'winman:diagnostics
        {--search= : Optional MO reference / product search filter}
        {--mo= : Internal WinMan ManufacturingOrder BIGINT to inspect components for}
        {--select : Persist the --mo selection and store a component snapshot}
        {--limit=15 : Maximum MOs to list}';

    protected $description = 'Verify WinMan connectivity and read outstanding MOs and live components.';

    public function handle(
        WinManConnection $connection,
        SearchOutstandingManufacturingOrdersJob $searchOrders,
        FetchManufacturingOrderComponentsJob $fetchComponents,
        SelectManufacturingOrderFeature $selectOrder,
    ): int {
        $this->info('WinMan Integration Diagnostics');
        $this->line("  Connection : {$connection->connectionName()}");
        $this->line("  Environment: {$connection->environment()}");
        $this->line("  Database   : {$connection->database()}");

        try {
            $connection->connection()->select('SELECT 1 AS ok');
            $this->info('  Connectivity: OK');
        } catch (Throwable $e) {
            $this->error('  Connectivity: FAILED - '.$e->getMessage());

            return self::FAILURE;
        }

        $moOption = $this->option('mo');

        if ($moOption === null) {
            $this->listOrders($searchOrders);

            return self::SUCCESS;
        }

        $winmanMo = (int) $moOption;

        if ($this->option('select')) {
            return $this->selectOrder($selectOrder, $winmanMo);
        }

        $this->inspectComponents($fetchComponents, $winmanMo);

        return self::SUCCESS;
    }

    private function listOrders(SearchOutstandingManufacturingOrdersJob $searchOrders): void
    {
        $search = $this->option('search');
        $limit = (int) $this->option('limit');

        $orders = $searchOrders($search, $limit);

        if ($orders === []) {
            $this->warn('No eligible outstanding MOs found. (ProductMaster WinMan mapping may be empty - see WM024.)');

            return;
        }

        $this->newLine();
        $this->table(
            ['MO (internal)', 'MO Ref', 'Type', 'Product', 'Outstanding', 'Description'],
            array_map(static fn (ManufacturingOrderData $o): array => [
                $o->winmanManufacturingOrder,
                $o->winmanManufacturingOrderId,
                $o->systemType,
                $o->winmanProductId,
                $o->quantityOutstanding,
                mb_strimwidth($o->productDescription, 0, 40, '...'),
            ], $orders),
        );
    }

    private function inspectComponents(FetchManufacturingOrderComponentsJob $fetchComponents, int $winmanMo): void
    {
        $components = $fetchComponents($winmanMo);

        if ($components === []) {
            $this->warn("No components found for MO {$winmanMo}.");

            return;
        }

        $this->newLine();
        $this->table(
            ['Type', 'Product', 'Classification', 'Issued', 'Outstanding', 'Description'],
            array_map(static fn (ComponentData $c): array => [
                $c->itemType,
                $c->winmanComponentProductId,
                $c->classification,
                $c->quantityIssued,
                $c->quantityOutstanding,
                mb_strimwidth($c->componentDescription, 0, 40, '...'),
            ], $components),
        );
    }

    private function selectOrder(SelectManufacturingOrderFeature $selectOrder, int $winmanMo): int
    {
        try {
            $order = $selectOrder($winmanMo);
        } catch (Throwable $e) {
            $this->error('Selection failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Selected MO {$order->mo_number} (local id {$order->id}).");
        $this->line('  Recipe code       : '.($order->recipe_code ?? '(unmapped)'));
        $this->line('  Component snapshot : '.$order->componentSnapshots->count().' rows');

        return self::SUCCESS;
    }
}
