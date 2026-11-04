<?php

namespace Tests\Feature;

use App\Models\SocialLink;
use App\Models\Venue;
use Database\Seeders\SocialLinkSeeder;
use Database\Seeders\VenueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenueProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_venues_are_seeded_and_rendered_on_the_public_map(): void
    {
        $this->seed(VenueSeeder::class);
        $this->seed(VenueSeeder::class);

        $this->assertDatabaseCount('venues', 1);
        $venue = Venue::query()->firstOrFail();
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $venue->ulid);
        $this->assertSame('ulid', $venue->getRouteKeyName());
        $this->assertStringContainsString($venue->ulid, route('venues.edit', $venue));
        $this->get('/')
            ->assertOk()
            ->assertSee('Kemang, Jakarta Selatan, DKI Jakarta')
            ->assertSee('Rally Padel');
    }

    public function test_venue_can_have_multiple_bank_accounts_with_ulids(): void
    {
        $venue = Venue::create([
            'name' => 'Venue rekening',
            'address' => 'Jakarta',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_active' => true,
        ]);
        $venue->bankAccounts()->createMany([
            ['bank_name' => 'BCA', 'account_name' => 'Pemilik Venue', 'account_number' => '0012345678'],
            ['bank_name' => 'Mandiri', 'account_name' => 'Pemilik Venue', 'account_number' => '9876543210'],
        ]);

        $this->assertDatabaseCount('venue_bank_accounts', 2);
        $this->assertCount(2, $venue->bankAccounts);
        foreach ($venue->bankAccounts as $account) {
            $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $account->ulid);
            $this->assertSame('ulid', $account->getRouteKeyName());
        }
        $this->assertSame('0012345678', $venue->bankAccounts->first()->account_number);
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
