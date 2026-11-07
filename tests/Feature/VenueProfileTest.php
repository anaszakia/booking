<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SocialLink;
use App\Models\User;
use App\Models\Venue;
use Database\Seeders\CourtSeeder;
use Database\Seeders\SocialLinkSeeder;
use Database\Seeders\VenueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VenueProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_venues_are_seeded_and_rendered_on_the_public_map(): void
    {
        $this->seed(VenueSeeder::class);
        $this->seed(VenueSeeder::class);
        $this->seed(CourtSeeder::class);

        $this->assertDatabaseCount('venues', 1);
        $venue = Venue::query()->firstOrFail();
        $venue->update(['logo_path' => 'venues/logo.png']);
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $venue->ulid);
        $this->assertSame('ulid', $venue->getRouteKeyName());
        $this->assertStringContainsString($venue->ulid, route('venues.edit', $venue));
        $this->get('/')
            ->assertOk()
            ->assertSee('Kemang, Jakarta Selatan, DKI Jakarta')
            ->assertSee('Rally Padel')
            ->assertDontSee('href="#keunggulan">Cara kerja', false)
            ->assertDontSee('href="#tentang">Tentang kami', false)
            ->assertSee('<option value="'.$venue->ulid.'">Rally Padel</option>', false)
            ->assertSee('Cari nama venue...', false)
            ->assertSee('Cari waktu...', false)
            ->assertSee('time-picker-options', false)
            ->assertSee('Pagi · 06.00–11.00', false)
            ->assertSee('Siang · 11.00–16.00', false)
            ->assertSee('Malam · 16.00–22.00', false)
            ->assertSee('</svg> Rally Padel</div>', false)
            ->assertSee('<link rel="icon" href="'.minio_url($venue->logo_path).'">', false);
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

    public function test_venue_hero_images_are_uploaded_and_rendered_as_slides(): void
    {
        config(['filesystems.minio_active' => false]);
        Storage::fake('public');
        $admin = $this->makeVenueAdmin();

        $this->withSession(['user_id' => $admin->id])
            ->post(route('venues.store'), [
                'name' => 'Venue Hero',
                'address' => 'Jakarta',
                'latitude' => -6.2,
                'longitude' => 106.8,
                'is_active' => '1',
                'hero_images' => [
                    UploadedFile::fake()->image('court-one.jpg'),
                    UploadedFile::fake()->image('court-two.jpg'),
                ],
            ])
            ->assertRedirect(route('venues.index'));

        $venue = Venue::query()->where('name', 'Venue Hero')->firstOrFail();
        $this->assertCount(2, $venue->heroImages);
        foreach ($venue->heroImages as $heroImage) {
            Storage::disk('public')->assertExists($heroImage->image_path);
        }

        $this->get('/')
            ->assertOk()
            ->assertSee('Pilih foto hero')
            ->assertSee(minio_url($venue->heroImages[0]->image_path), false)
            ->assertSee(minio_url($venue->heroImages[1]->image_path), false)
            ->assertSee('setInterval', false);
    }

    public function test_venue_hero_images_can_be_removed_and_replaced(): void
    {
        config(['filesystems.minio_active' => false]);
        Storage::fake('public');
        $venue = Venue::create([
            'name' => 'Venue Update Hero',
            'address' => 'Jakarta',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_active' => true,
        ]);
        $firstImage = $venue->heroImages()->create(['image_path' => 'venues/hero/first.jpg', 'sort_order' => 0]);
        $secondImage = $venue->heroImages()->create(['image_path' => 'venues/hero/second.jpg', 'sort_order' => 1]);
        Storage::disk('public')->put($firstImage->image_path, 'old image');
        Storage::disk('public')->put($secondImage->image_path, 'old image');
        $admin = $this->makeVenueAdmin();

        $this->withSession(['user_id' => $admin->id])
            ->put(route('venues.update', $venue), [
                'name' => $venue->name,
                'address' => $venue->address,
                'latitude' => $venue->latitude,
                'longitude' => $venue->longitude,
                'is_active' => '1',
                'delete_hero_images' => [$firstImage->id],
                'hero_images' => [UploadedFile::fake()->image('replacement.jpg')],
            ])
            ->assertRedirect(route('venues.index'));

        $venue->refresh();
        $this->assertCount(2, $venue->heroImages);
        $this->assertDatabaseMissing('venue_hero_images', ['id' => $firstImage->id]);
        $this->assertDatabaseHas('venue_hero_images', ['id' => $secondImage->id]);
        Storage::disk('public')->assertMissing($firstImage->image_path);
    }

    public function test_venue_theme_colors_can_be_updated_and_are_rendered_on_the_frontend(): void
    {
        $venue = Venue::create([
            'name' => 'Venue Tema',
            'address' => 'Jakarta',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_active' => true,
        ]);
        $admin = $this->makeVenueAdmin();

        $this->withSession(['user_id' => $admin->id])
            ->put(route('venues.update', $venue), [
                'name' => $venue->name,
                'address' => $venue->address,
                'latitude' => $venue->latitude,
                'longitude' => $venue->longitude,
                'primary_color' => '#185ADB',
                'accent_color' => '#FFB703',
                'is_active' => '1',
            ])
            ->assertRedirect(route('venues.index'));

        $this->assertDatabaseHas('venues', [
            'id' => $venue->id,
            'primary_color' => '#185ADB',
            'accent_color' => '#FFB703',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('--venue-primary: #185ADB', false)
            ->assertSee('--venue-accent: #FFB703', false);

        $this->get(route('schedule'))
            ->assertOk()
            ->assertSee('--venue-primary: #185ADB', false)
            ->assertSee('--venue-accent: #FFB703', false)
            ->assertSee('--brand: var(--venue-primary)', false)
            ->assertSee('--accent: var(--venue-accent)', false);

        $this->get(route('booking.upload-form'))
            ->assertOk()
            ->assertSee('--venue-primary: #185ADB', false)
            ->assertSee('--venue-accent: #FFB703', false)
            ->assertSee('--brand:var(--venue-primary)', false)
            ->assertSee('--accent:var(--venue-accent)', false);
    }

    public function test_venue_theme_colors_must_be_six_digit_hex_values(): void
    {
        $venue = Venue::create([
            'name' => 'Venue Warna Invalid',
            'address' => 'Jakarta',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_active' => true,
        ]);
        $admin = $this->makeVenueAdmin();

        $this->withSession(['user_id' => $admin->id])
            ->put(route('venues.update', $venue), [
                'name' => $venue->name,
                'address' => $venue->address,
                'latitude' => $venue->latitude,
                'longitude' => $venue->longitude,
                'primary_color' => 'red',
                'accent_color' => '#FFB703',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('primary_color');

        $this->assertSame('#245c43', $venue->fresh()->primary_color);
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

    private function makeVenueAdmin(): User
    {
        $role = Role::firstOrCreate(['slug' => 'superadmin'], ['name' => 'SuperAdmin']);
        $user = User::create([
            'name' => 'Venue Admin',
            'email' => 'venue-admin-'.uniqid().'@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->attach($role);

        return $user;
    }
}
