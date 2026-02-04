<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductReview extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = [
        'product_id',
        'user_id',
        'order_item_id',
        'rating',
        'title',
        'comment',
        'is_verified_purchase',
        'is_approved',
        'helpful_count',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_verified_purchase' => 'boolean',
            'is_approved' => 'boolean',
            'helpful_count' => 'integer',
        ];
    }

    /**
     * Get the product this review belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who wrote this review
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order item this review is for (verified purchase)
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get all images for this review
     */
    public function images(): HasMany
    {
        return $this->hasMany(ReviewImage::class)->orderBy('sort_order');
    }

    /**
     * Increment helpful count
     */
    public function markAsHelpful(): void
    {
        $this->increment('helpful_count');
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg']);
    }

    /**
     * Register media conversions
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->nonQueued();

        $this
            ->addMediaConversion('medium')
            ->width(500)
            ->height(500)
            ->nonQueued();
    }
}
