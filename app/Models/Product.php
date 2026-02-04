<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'features',
        'base_price',
        'featured_image',
        'is_featured',
        'is_active',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'base_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'view_count' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('featured_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg']);

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

        $this
            ->addMediaConversion('large')
            ->width(1200)
            ->height(1200);
    }

    /**
     * Get the category this product belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all images for this product
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Get all variants for this product
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get all active discounts for this product
     */
    public function discounts(): HasMany
    {
        return $this->hasMany(ProductDiscount::class);
    }

    /**
     * Get all brands associated with this product
     */
    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class);
    }

    /**
     * Get all leagues associated with this product
     */
    public function leagues(): BelongsToMany
    {
        return $this->belongsToMany(League::class);
    }

    /**
     * Get all teams associated with this product
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }

    /**
     * Get all surface types associated with this product
     */
    public function surfaceTypes(): BelongsToMany
    {
        return $this->belongsToMany(SurfaceType::class);
    }

    /**
     * Get all reviews for this product
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Get all users who wishlisted this product
     */
    public function wishlistedBy(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }
}
