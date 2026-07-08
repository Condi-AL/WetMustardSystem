<?php

namespace App\Domains\WinMan\Jobs;

use App\Domains\WinMan\Data\ManufacturingOrderData;
use App\Domains\WinMan\Support\WinManConnection;
use App\Models\Product;

/**
 * Fetches outstanding, eligible WinMan manufacturing orders (scope §11.2).
 *
 * Eligibility: positive outstanding quantity, an eligible SystemType (firm /
 * in-progress / released), and a product code that exists in the DBMTS
 * ProductMaster WinMan mapping. Read-only; DBMTS never creates WinMan MOs.
 *
 * @return array<int, ManufacturingOrderData>
 */
class SearchOutstandingManufacturingOrdersJob
{
    public function __construct(
        private readonly WinManConnection $winman,
    ) {
    }

    /**
     * @return array<int, ManufacturingOrderData>
     */
    public function __invoke(?string $search = null, int $limit = 50): array
    {
        $mappedProductIds = $this->mappedProductIds();

        if ($mappedProductIds === []) {
            return [];
        }

        $eligibleTypes = array_values((array) config('winman.eligible_system_types', ['F', 'I', 'R']));

        $bindings = [$limit];
        $typePlaceholders = $this->placeholders($eligibleTypes, $bindings);
        $productPlaceholders = $this->placeholders($mappedProductIds, $bindings);

        $searchClause = '';
        if ($search !== null && trim($search) !== '') {
            $like = '%'.trim($search).'%';
            $searchClause = ' AND (mo.ManufacturingOrderId LIKE ? OR p.ProductId LIKE ? OR p.ProductDescription LIKE ?)';
            array_push($bindings, $like, $like, $like);
        }

        $sql = "SELECT TOP (?) mo.ManufacturingOrder, mo.ManufacturingOrderId, mo.Product,
                   mo.SystemType, mo.Quantity, mo.QuantityOutstanding, mo.DueDate, mo.LastModifiedDate,
                   p.ProductId, p.ProductDescription, p.Classification, p.UnitOfMeasure
                FROM ManufacturingOrders mo
                JOIN Products p ON p.Product = mo.Product
                WHERE mo.QuantityOutstanding > 0
                  AND mo.SystemType IN ({$typePlaceholders})
                  AND p.ProductId IN ({$productPlaceholders})
                  {$searchClause}
                ORDER BY mo.DueDate ASC, mo.ManufacturingOrder DESC";

        $rows = $this->winman->connection()->select($sql, $bindings);

        return array_map(
            static fn (object $row): ManufacturingOrderData => ManufacturingOrderData::fromRow($row),
            $rows,
        );
    }

    /**
     * WinMan ProductId values mapped in the DBMTS ProductMaster.
     *
     * @return array<int, string>
     */
    private function mappedProductIds(): array
    {
        return Product::query()
            ->where('active_flag', true)
            ->where(function ($query): void {
                $query->whereNotNull('winman_product_id')
                    ->orWhereNotNull('finished_goods_code');
            })
            ->get(['winman_product_id', 'finished_goods_code'])
            ->flatMap(fn (Product $product): array => [
                $product->winman_product_id,
                $product->finished_goods_code,
            ])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Append values to $bindings and return the matching placeholder list.
     *
     * @param  array<int, mixed>  $values
     * @param  array<int, mixed>  $bindings
     */
    private function placeholders(array $values, array &$bindings): string
    {
        foreach ($values as $value) {
            $bindings[] = $value;
        }

        return implode(', ', array_fill(0, count($values), '?'));
    }
}
