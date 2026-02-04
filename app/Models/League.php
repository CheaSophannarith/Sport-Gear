<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class League extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo',
        'country',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($league) {
            if (empty($league->slug)) {
                $league->slug = Str::slug($league->name);
            }
        });
    }

    /**
     * Get all teams in this league
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }
}
