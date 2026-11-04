<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\Venue;
use Database\Seeders\CourtSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourtTest extends TestCase
{
    use RefreshDatabase;

    public function test_court_master_can_store_and_filter_active_courts(): void
    {
        $venue = $this->makeVenue('Venue Padel', 'Alamat venue uji');
        $activeCourt = Court::create([
            'name' => 'Padel Kemang',
            'venue_id' => $venue->id,
            'court_type' => 'indoor',
            'price_per_hour' => 250000,
            'is_active' => true,
        ]);
        Court::create([
            'name' => 'Padel Puri',
            'venue_id' => $venue->id,
            'court_type' => 'outdoor',
            'price_per_hour' => 200000,
            'is_active' => false,
        ]);

        $this->assertSame(250000, $activeCourt->price_per_hour);
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $activeCourt->ulid);
        $this->assertSame('ulid', $activeCourt->getRouteKeyName());
        $this->assertStringContainsString($activeCourt->ulid, route('courts.edit', $activeCourt));
        $this->assertSame($activeCourt->id, Court::resolveRouteBinding($activeCourt->ulid)?->id);
        $this->assertCount(1, Court::active()->get());
        $this->assertSame('Padel Kemang', Court::active()->first()->name);
    }

    public function test_court_seeder_is_idempotent_and_public_pages_use_active_master_data(): void
    {
        $venue = $this->makeVenue('Venue Seeder', 'Alamat venue seeder');
        Venue::query()->whereKey($venue->id)->update(['is_active' => true]);
        $this->seed(CourtSeeder::class);
        $this->seed(CourtSeeder::class);
        Court::create([
            'name' => 'Lapangan Arsip',
            'venue_id' => $venue->id,
            'court_type' => 'indoor',
            'price_per_hour' => 100000,
            'is_active' => false,
        ]);

        $this->assertDatabaseCount('courts', 4);
        $this->get('/')
            ->assertOk()
            ->assertSee('Padel Social Club')
            ->assertSee($activeCourtUlid = Court::where('name', 'Padel Social Club')->value('ulid'))
            ->assertSee('Alamat venue seeder')
            ->assertDontSee('Lapangan Arsip');
        $this->get('/jadwal')
            ->assertOk()
            ->assertSee('Padel Social Club')
            ->assertSee($activeCourtUlid)
            ->assertDontSee('Lapangan Arsip');
    }

    private function makeVenue(string $name, string $address): Venue
    {
        return Venue::create([
            'name' => $name,
            'address' => $address,
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_active' => true,
        ]);
    }
}
