<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DBMTS Recipe Ingredient definition (scope entity: RecipeIngredient).
 */
class RecipeIngredient extends Model
{
    protected $fillable = [
        'recipe_id',
        'variant_id',
        'material_code',
        'material_description',
        'percentage',
        'required_quantity',
        'uom',
        'sequence',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:4',
            'required_quantity' => 'decimal:3',
            'sequence' => 'integer',
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(RecipeVariant::class, 'variant_id');
    }
}
