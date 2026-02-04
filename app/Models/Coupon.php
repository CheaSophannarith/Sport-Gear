<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'min_purchase_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_purchase_amount' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if coupon is currently valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount for a given subtotal
     */
    public function calculateDiscount(float $subtotal): float
    {
        if (!$this->isValid()) {
            return 0.00;
        }

        if ($this->min_purchase_amount && $subtotal < $this->min_purchase_amount) {
            return 0.00;
        }

        $discount = 0.00;

        if ($this->discount_type === 'percentage') {
            $discount = $subtotal * ($this->discount_value / 100);
            if ($this->max_discount_amount) {
                $discount = min($discount, $this->max_discount_amount);
            }
        } else {
            $discount = $this->discount_value;
        }

        return min($discount, $subtotal);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
