<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * DEV ONLY. Provisional WinMan finished-goods mappings verified from live WinMan
 * data, so the MO Search screen shows real outstanding MOs before WM024 is
 * loaded. NOT registered in DatabaseSeeder; run explicitly in dev:
 *
 *   php artisan db:seed --class=DevWinManMappingSeeder
 *
 * These mappings are replaced by the authoritative WM024 product master.
 *
 * NOTE: These codes are the PreRelease outstanding finished-goods products
 * (WINMAN_ENVIRONMENT=prerelease). Condimentum uses different FG codes.
 */
class DevWinManMappingSeeder extends Seeder
{
    /**
     * recipe_code => WinMan finished-goods ProductId (verified outstanding in PreRelease).
     */
    private const MAPPINGS = [
        '30010001' => '70010026', // Condimentum - Wholegrain Mustard CPM001WG (10kg)
        '30010010' => '70010038', // Condimentum - Dijon Mustard CPM001 (10kg)
        '30010009' => '50010018', // Condimentum - English Mustard
        '30010023' => '70010076', // American Style Mustard CSS CPM004A (IBC)
    ];

    public function run(): void
    {
        // Reset any prior dev mappings so only the current set is active.
        Product::query()->update([
            'finished_goods_code' => null,
            'winman_product_id' => null,
        ]);

        foreach (self::MAPPINGS as $recipeCode => $winmanProductId) {
            Product::query()
                ->where('recipe_code', $recipeCode)
                ->update([
                    'finished_goods_code' => $winmanProductId,
                    'winman_product_id' => $winmanProductId,
                ]);
        }
    }
}
