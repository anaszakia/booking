<?php

namespace Tests\Feature;

use App\Models\Court;
use Database\Seeders\CourtSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourtTest extends TestCase
{
    use RefreshDatabase;

    public function test_court_master_can_store_and_filter_active_courts(): void
    {
        $activeCourt = Court::create([
            'name' => 'Padel Kemang',
            'location' => 'Kemang, Jakarta Selatan',
            'court_type' => 'indoor',
            'price_per_hour' => 250000,
            'is_active' => true,
        ]);
        Court::create([
            'name' => 'Padel Puri',
            'location' => 'Puri Indah, Jakarta Barat',
            'court_type' => 'outdoor',
            'price_per_hour' => 200000,
            'is_active' => false,
        ]);

        $this->assertSame(250000, $activeCourt->price_per_hour);
        $this->assertCount(1, Court::active()->get());
        $this->assertSame('Padel Kemang', Court::active()->first()->name);
    }

    public function test_court_seeder_is_idempotent_and_public_pages_use_active_master_data(): void
    {
        $this->seed(CourtSeeder::class);
        $this->seed(CourtSeeder::class);
        Court::create([
            'name' => 'Lapangan Arsip',
            'location' => 'Jakarta',
            'court_type' => 'indoor',
            'price_per_hour' => 100000,
            'is_active' => false,
        ]);

        $this->assertDatabaseCount('courts', 4);
        $this->get('/')
            ->assertOk()
            ->assertSee('Padel Social Club')
            ->assertDontSee('Lapangan Arsip');
        $this->get('/jadwal')
            ->assertOk()
            ->assertSee('Padel Social Club')
            ->assertDontSee('Lapangan Arsip');
    }
}
