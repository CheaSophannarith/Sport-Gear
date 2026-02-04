<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SurfaceType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
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

        static::creating(function ($surfaceType) {
            if (empty($surfaceType->slug)) {
                $surfaceType->slug = Str::slug($surfaceType->name);
            }
        });
    }
}
