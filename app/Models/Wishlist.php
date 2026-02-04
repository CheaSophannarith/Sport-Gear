<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'product_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($wishlist) {
            if (empty($wishlist->created_at)) {
                $wishlist->created_at = now();
            }
        });
    }

    /**
     * Get the user this wishlist item belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product in this wishlist
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
