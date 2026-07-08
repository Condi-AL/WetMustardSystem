<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DBMTS Product Master (scope entity: ProductMaster).
 *
 * WinMan mapping path: recipe_code -> finished_goods_code -> winman_product_id.
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_code',
        'product_name',
        'finished_goods_code',
        'intermediate_code',
        'winman_product_id',
        'winman_product_internal',
        'winman_pack_size',
        'shelf_life_days',
        'pack_format',
        'pallet_type',
        'amount_per_pallet',
        'customer',
        'active_flag',
    ];

    protected function casts(): array
    {
        return [
            'winman_pack_size' => 'decimal:3',
            'shelf_life_days' => 'integer',
            'amount_per_pallet' => 'integer',
            'active_flag' => 'boolean',
        ];
    }

    /**
     * Recipe header(s) sharing this product's recipe code.
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class, 'recipe_code', 'recipe_code');
    }

    /**
     * Batch-size variants available for this product's recipe.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(RecipeVariant::class, 'recipe_code', 'recipe_code');
    }
}
