<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Uid\Ulid;

class Venue extends Model
{
    protected $fillable = [
        'name',
        'logo_path',
        'qris_path',
        'primary_color',
        'accent_color',
        'hero_eyebrow',
        'hero_title',
        'hero_title_highlight',
        'hero_subtitle',
        'address',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function (Venue $venue): void {
            $venue->ulid ??= (string) new Ulid;
        });
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(VenueBankAccount::class);
    }

    public function heroImages(): HasMany
    {
        return $this->hasMany(VenueHeroImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function courts(): HasMany
    {
        return $this->hasMany(Court::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
