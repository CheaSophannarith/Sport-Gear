<?php

namespace App\Enums;

enum LoyaltyTier: string
{
    case NONE = 'none';
    case SILVER = 'silver';
    case GOLD = 'gold';

    /**
     * Get the discount percentage for this tier
     */
    public function discountPercentage(): int
    {
        return match ($this) {
            self::NONE => 0,
            self::SILVER => 5,
            self::GOLD => 10,
        };
    }

    /**
     * Get the minimum spending required for this tier
     */
    public function minimumSpending(): float
    {
        return match ($this) {
            self::NONE => 0,
            self::SILVER => 50.00,
            self::GOLD => 100.00,
        };
    }

    /**
     * Get the tier label
     */
    public function label(): string
    {
        return match ($this) {
            self::NONE => 'No Tier',
            self::SILVER => 'Silver',
            self::GOLD => 'Gold',
        };
    }

    /**
     * Calculate tier based on total spent
     */
    public static function calculateTier(float $totalSpent): self
    {
        return match (true) {
            $totalSpent >= 100.00 => self::GOLD,
            $totalSpent >= 50.00 => self::SILVER,
            default => self::NONE,
        };
    }
}
