<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Ulid;

class VenueSetting extends Model
{
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'map_zoom',
    ];

    protected static function booted(): void
    {
        static::creating(function (VenueSetting $venueSetting): void {
            $venueSetting->ulid ??= (string) new Ulid;
        });
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'map_zoom' => 'integer',
        ];
    }

    public static function defaults(): array
    {
        return [
            'name' => 'Rally Padel',
            'address' => 'Kemang, Jakarta Selatan, DKI Jakarta',
            'latitude' => -6.2607,
            'longitude' => 106.8167,
            'map_zoom' => 14,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
