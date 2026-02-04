<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_variant_id',
        'quantity',
        'price_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price_snapshot' => 'decimal:2',
        ];
    }

    /**
     * Get the cart this item belongs to
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product variant for this cart item
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get the subtotal for this item
     */
    public function getSubtotalAttribute(): float
    {
        return $this->price_snapshot * $this->quantity;
    }

    /**
     * Check if the variant is still in stock with the requested quantity
     */
    public function hasAvailableStock(): bool
    {
        return $this->productVariant->stock_quantity >= $this->quantity;
    }
}
