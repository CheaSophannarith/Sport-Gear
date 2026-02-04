<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'size',
        'price_adjustment',
        'stock_quantity',
        'low_stock_threshold',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_adjustment' => 'decimal:2',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the product this variant belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the final price for this variant
     */
    public function getFinalPriceAttribute(): float
    {
        return $this->product->base_price + $this->price_adjustment;
    }

    /**
     * Check if variant is in stock
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0 && $this->is_active;
    }

    /**
     * Check if variant is low stock
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold && $this->stock_quantity > 0;
    }
}
