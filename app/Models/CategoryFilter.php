<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryFilter extends Model
{
    protected $fillable = [
        'category_id',
        'filter_type',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the category this filter belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
