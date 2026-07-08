<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DBMTS Recipe Batch Variant (scope entity: RecipeBatchVariant).
 *
 * An approved batch-size variant for a recipe. Recipe and batch size are
 * deliberately separate: the same recipe can have several approved batch sizes.
 */
class RecipeVariant extends Model
{
    protected $fillable = [
        'recipe_id',
        'recipe_code',
        'variant_name',
        'batch_size',
        'batch_size_uom',
        'plc_recipe_number',
        'source_document_code',
        'active_flag',
    ];

    protected function casts(): array
    {
        return [
            'batch_size' => 'decimal:3',
            'active_flag' => 'boolean',
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class, 'variant_id');
    }

    public function sourceDocument(): BelongsTo
    {
        return $this->belongsTo(DocumentReference::class, 'source_document_code', 'code');
    }
}
