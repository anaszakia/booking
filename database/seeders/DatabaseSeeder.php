<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            MenuSeeder::class,
            VenueSeeder::class,
            CourtSeeder::class,
            SocialLinkSeeder::class,
        ]);
    }
}
