<?php

namespace Database\Seeders;

use App\Models\Court;
use Illuminate\Database\Seeder;

class CourtSeeder extends Seeder
{
    public function run(): void
    {
        $courts = [
            [
                'name' => 'Padel Social Club',
                'location' => 'Kemang, Jakarta Selatan',
                'address' => 'Kemang, Jakarta Selatan, DKI Jakarta',
                'court_type' => 'indoor',
                'price_per_hour' => 250000,
                'description' => 'Lapangan indoor untuk bermain nyaman sepanjang hari.',
                'image_url' => 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=900&q=80',
                'is_active' => true,
            ],
            [
                'name' => 'Padel Haus',
                'location' => 'Puri Indah, Jakarta Barat',
                'address' => 'Puri Indah, Jakarta Barat, DKI Jakarta',
                'court_type' => 'outdoor',
                'price_per_hour' => 200000,
                'description' => 'Lapangan outdoor dengan suasana terbuka.',
                'image_url' => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=900&q=80',
                'is_active' => true,
            ],
            [
                'name' => 'The Court Society',
                'location' => 'Pluit, Jakarta Utara',
                'address' => 'Pluit, Jakarta Utara, DKI Jakarta',
                'court_type' => 'indoor',
                'price_per_hour' => 275000,
                'description' => 'Lapangan indoor untuk sesi latihan dan pertandingan.',
                'image_url' => 'https://images.unsplash.com/photo-1595435934249-5df7ed86e1c0?auto=format&fit=crop&w=900&q=80',
                'is_active' => true,
            ],
        ];

        foreach ($courts as $court) {
            Court::updateOrCreate(
                ['name' => $court['name']],
                $court
            );
        }
    }
}
