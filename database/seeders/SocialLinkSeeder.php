<?php

namespace Database\Seeders;

use App\Models\SocialLink;
use Illuminate\Database\Seeder;

class SocialLinkSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Instagram', 'platform' => 'instagram', 'url' => 'https://www.instagram.com/'],
            ['name' => 'TikTok', 'platform' => 'tiktok', 'url' => 'https://www.tiktok.com/'],
        ] as $index => $link) {
            SocialLink::firstOrCreate(
                ['platform' => $link['platform'], 'url' => $link['url']],
                [...$link, 'is_active' => false, 'sort_order' => $index + 1]
            );
        }
    }
}
