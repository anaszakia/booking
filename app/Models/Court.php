<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    protected $fillable = [
        'name',
        'location',
        'address',
        'court_type',
        'price_per_hour',
        'description',
        'image_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_per_hour' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
