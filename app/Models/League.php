<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class League extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'country',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($league) {
            if (empty($league->slug)) {
                $league->slug = Str::slug($league->name);
            }
        });

        static::updating(function ($league) {
            if (empty($league->slug)) {
                $league->slug = Str::slug($league->name);
            }
        });
    }

    /**
     * Get all products for this league
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all teams in this league
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }
}
