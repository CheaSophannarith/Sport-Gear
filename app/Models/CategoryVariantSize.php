<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryVariantSize extends Model
{
    protected $fillable = [
        'category_id',
        'size_value',
        'display_label',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the category this variant size belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
