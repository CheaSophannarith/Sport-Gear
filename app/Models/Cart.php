<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'merged_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'merged_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user this cart belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all items in this cart
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the total price of all items in the cart
     */
    public function getTotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->price_snapshot * $item->quantity;
        });
    }

    /**
     * Get the total number of items in the cart
     */
    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Check if cart is expired (for guest carts)
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return now()->gt($this->expires_at);
    }
}
