<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Ulid;

class SocialLink extends Model
{
    protected $fillable = [
        'name',
        'platform',
        'url',
        'is_active',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::creating(function (SocialLink $socialLink): void {
            $socialLink->ulid ??= (string) new Ulid;
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
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
