<?php

namespace Database\Seeders;

use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    public function run(): void
    {
        Venue::firstOrCreate(
            ['name' => 'Rally Padel'],
            [
                'address' => 'Kemang, Jakarta Selatan, DKI Jakarta',
                'latitude' => -6.2607,
                'longitude' => 106.8167,
                'is_active' => true,
            ]
        );
    }
}
