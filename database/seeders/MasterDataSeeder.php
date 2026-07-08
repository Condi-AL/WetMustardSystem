<?php

namespace Database\Seeders;

use App\Models\DocumentReference;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeVariant;
use Illuminate\Database\Seeder;

/**
 * Seeds DBMTS master data that is stated as fact in the functional spec (§3).
 *
 * Sourced only from the scope document. WinMan identifiers (finished_goods_code,
 * winman_product_id, pack size) and batch sizes not documented in scope are left
 * null pending WM024 (Wet Mustard Product Codes) and the batchcard PDFs.
 */
class MasterDataSeeder extends Seeder
{
    /**
     * Controlled source documents (scope §3). [code => [title, module]].
     */
    private const DOCUMENTS = [
        'WM004' => ['Wet Mustard Pallecon Filling / Processing', 'Pallecon Filling'],
        'WM011' => ['Metal Detector Verification Sheet', 'Metal Detector'],
        'WM014' => ['Primary Packaging Records - Drums', 'Packaging Traceability'],
        'WM015' => ['Primary Packaging Records - Buckets & Lids', 'Packaging Traceability'],
        'WM016' => ['Wet Mustard Bucket Processing 10kg', 'Bucket Packing'],
        'WM018' => ['30010004 Kerry Dusseldorf Mustard Batchcard', 'Batchcard'],
        'WM019' => ['30010002 Condi American Mustard CPM001A Batchcard', 'Batchcard'],
        'WM020' => ['30010001 Condi Wholegrain Mustard CPM001WG Batchcard', 'Batchcard'],
        'WM021' => ['30010009 Condi English Mustard CPM001E Batchcard', 'Batchcard'],
        'WM022' => ['30010010 Condi Dijon Mustard CPM001 Batchcard', 'Batchcard'],
        'WM023' => ['30010003 Condi Dijon Style Mustard CPM001DS Batchcard', 'Batchcard'],
        'WM024' => ['Wet Mustard Product Codes', 'Product Master'],
        'WM025' => ['30010018 Dijon Mustard CPM002 Batchcard', 'Batchcard'],
        'WM034' => ['30010016 BPS Poshdog Mustard Batchcard', 'Batchcard'],
        'WM036' => ['30010019 Condi French Mustard Batchcard', 'Batchcard'],
        'WM039' => ['30010023 American Style Mustard CSS CPM004A Batchcard', 'Batchcard'],
        'WM042' => ['30010025 BPS M&S English Mustard Batchcard', 'Batchcard'],
        'WM044' => ['30010022 American Style Mustard NAS CPM003A Batchcard', 'Batchcard'],
        'WM045' => ['30010023 American Style Mustard CSS CPM004A Batchcard 500kg', 'Batchcard'],
        'WM046' => ['Wet Mustard Drum Processing', 'Drum Processing'],
        'WM047' => ['Primary Packaging Records - Buckets & Lids (NVE)', 'Packaging Traceability'],
        'WM048' => ['30010026 Table Mustard CPM001M Batchcard 800kg', 'Batchcard'],
        'WM049' => ['30010026 Table Mustard CPM001M Batchcard 400kg', 'Batchcard'],
    ];

    /**
     * Products / recipes (scope §3). [recipe_code => [product_name, source_doc]].
     */
    private const PRODUCTS = [
        '30010004' => ['Kerry Dusseldorf Mustard', 'WM018'],
        '30010002' => ['Condi American Mustard CPM001A', 'WM019'],
        '30010001' => ['Condi Wholegrain Mustard CPM001WG', 'WM020'],
        '30010009' => ['Condi English Mustard CPM001E', 'WM021'],
        '30010010' => ['Condi Dijon Mustard CPM001', 'WM022'],
        '30010003' => ['Condi Dijon Style Mustard CPM001DS', 'WM023'],
        '30010018' => ['Dijon Mustard CPM002', 'WM025'],
        '30010016' => ['BPS Poshdog Mustard', 'WM034'],
        '30010019' => ['Condi French Mustard', 'WM036'],
        '30010023' => ['American Style Mustard CSS CPM004A', 'WM039'],
        '30010025' => ['BPS M&S English Mustard', 'WM042'],
        '30010022' => ['American Style Mustard NAS CPM003A', 'WM044'],
        '30010026' => ['Table Mustard CPM001M', 'WM048'],
    ];

    /**
     * Batch-size variants explicitly documented in scope.
     * [recipe_code => [ [variant_name, batch_size, source_doc], ... ]].
     */
    private const VARIANTS = [
        '30010023' => [['CPM004A 500kg', 500, 'WM045']],
        '30010026' => [['CPM001M 800kg', 800, 'WM048'], ['CPM001M 400kg', 400, 'WM049']],
    ];

    public function run(): void
    {
        foreach (self::DOCUMENTS as $code => [$title, $module]) {
            DocumentReference::updateOrCreate(
                ['code' => $code],
                ['title' => $title, 'module' => $module, 'status' => 'Active'],
            );
        }

        foreach (self::PRODUCTS as $recipeCode => [$productName, $sourceDoc]) {
            Product::updateOrCreate(
                ['recipe_code' => $recipeCode],
                ['product_name' => $productName, 'active_flag' => true],
            );

            $recipe = Recipe::updateOrCreate(
                ['recipe_code' => $recipeCode, 'revision' => null],
                ['source_document_code' => $sourceDoc, 'active_flag' => true],
            );

            foreach (self::VARIANTS[$recipeCode] ?? [] as [$variantName, $batchSize, $variantDoc]) {
                RecipeVariant::updateOrCreate(
                    ['recipe_code' => $recipeCode, 'variant_name' => $variantName],
                    [
                        'recipe_id' => $recipe->id,
                        'batch_size' => $batchSize,
                        'batch_size_uom' => 'kg',
                        'source_document_code' => $variantDoc,
                        'active_flag' => true,
                    ],
                );
            }
        }
    }
}
