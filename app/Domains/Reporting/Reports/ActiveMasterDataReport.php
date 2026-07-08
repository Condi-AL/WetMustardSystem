<?php

namespace App\Domains\Reporting\Reports;

use App\Models\Product;
use Carbon\CarbonInterface;

/**
 * Active products, recipes and variants master-data listing (report key:
 * dbmts_active_master_data, scope §14.2).
 */
class ActiveMasterDataReport extends AbstractReport
{
    public const KEY = 'dbmts_active_master_data';

    public function key(): string
    {
        return self::KEY;
    }

    public function name(): string
    {
        return 'Active Products, Recipes and Variants';
    }

    protected function data(CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = Product::query()
            ->with('variants')
            ->where('active_flag', true)
            ->orderBy('product_name')
            ->get()
            ->map(fn (Product $p): array => [
                $p->recipe_code,
                $p->product_name,
                $p->finished_goods_code ?? '—',
                $p->winman_product_id ?? '—',
                $p->variants->count(),
            ])
            ->all();

        return [
            'headers' => ['Recipe', 'Product', 'FG code', 'WinMan ProductId', 'Variants'],
            'rows' => $rows,
            'summary' => count($rows).' active product(s).',
        ];
    }
}
