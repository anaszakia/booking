<?php

namespace Tests\Feature;

use App\Models\SocialLink;
use App\Models\VenueSetting;
use Database\Seeders\SocialLinkSeeder;
use Database\Seeders\VenueSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenueProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_settings_are_seeded_and_rendered_on_the_public_map(): void
    {
        $this->seed(VenueSettingSeeder::class);
        $this->seed(VenueSettingSeeder::class);

        $this->assertDatabaseCount('venue_settings', 1);
        $settings = VenueSetting::query()->firstOrFail();
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $settings->ulid);
        $this->assertSame('ulid', $settings->getRouteKeyName());
        $this->assertStringContainsString($settings->ulid, route('venue-settings.edit', $settings));
        $this->get('/')
            ->assertOk()
            ->assertSee('Kemang, Jakarta Selatan, DKI Jakarta')
            ->assertSee('-6.2607')
            ->assertSee('106.8167');
    }

    public function test_social_links_are_seeded_inactive_and_can_be_selected_for_public_display(): void
    {
        $this->seed(SocialLinkSeeder::class);
        $this->seed(SocialLinkSeeder::class);

        $this->assertDatabaseCount('social_links', 2);
        $socialLink = SocialLink::query()->firstOrFail();
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $socialLink->ulid);
        $this->assertSame('ulid', $socialLink->getRouteKeyName());
        $this->assertStringContainsString($socialLink->ulid, route('social-links.edit', $socialLink));
        $this->assertCount(0, SocialLink::active()->get());

        SocialLink::where('platform', 'instagram')->update(['is_active' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Instagram')
            ->assertDontSee('TikTok');
    }
}
