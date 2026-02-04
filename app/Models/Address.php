<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'province_id',
        'label',
        'recipient_name',
        'phone',
        'street_address',
        'village',
        'district',
        'notes',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this address
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the province for this address
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the full formatted address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street_address,
            $this->village,
            $this->district,
            $this->province->name ?? null,
        ]);

        return implode(', ', $parts);
    }
}
