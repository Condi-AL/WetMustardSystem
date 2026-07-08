<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DBMTS Recipe Header (scope entity: RecipeHeader).
 */
class Recipe extends Model
{
    protected $fillable = [
        'recipe_code',
        'revision',
        'issue_date',
        'plc_recipe_number',
        'source_document_code',
        'active_flag',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'active_flag' => 'boolean',
        ];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(RecipeVariant::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function sourceDocument(): BelongsTo
    {
        return $this->belongsTo(DocumentReference::class, 'source_document_code', 'code');
    }
}
