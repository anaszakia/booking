<?php

namespace Database\Seeders;

use App\Models\VenueSetting;
use Illuminate\Database\Seeder;

class VenueSettingSeeder extends Seeder
{
    public function run(): void
    {
        VenueSetting::query()->first() ?? VenueSetting::create(VenueSetting::defaults());
    }
}
