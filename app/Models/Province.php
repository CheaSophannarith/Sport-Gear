<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    protected $fillable = [
        'name',
        'shipping_fee',
        'is_cod_available',
    ];

    protected function casts(): array
    {
        return [
            'shipping_fee' => 'decimal:2',
            'is_cod_available' => 'boolean',
        ];
    }

    /**
     * Get all addresses in this province
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
