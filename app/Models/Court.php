<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Ulid;

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

    protected static function booted(): void
    {
        static::creating(function (Court $court): void {
            $court->ulid ??= (string) new Ulid;
        });
    }

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

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
