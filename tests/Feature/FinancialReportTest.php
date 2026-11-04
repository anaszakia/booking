<?php

namespace Tests\Feature;

use App\Http\Controllers\FinancialReportController;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FinancialReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_counts_only_confirmed_bookings_verified_in_the_selected_period(): void
    {
        $venue = Venue::create([
            'name' => 'Venue Rekap',
            'address' => 'Jakarta',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_active' => true,
        ]);
        $court = Court::create([
            'name' => 'Lapangan Rekap',
            'venue_id' => $venue->id,
            'court_type' => 'indoor',
            'price_per_hour' => 250000,
            'is_active' => true,
        ]);

        $this->createBooking($court, $venue, 'confirmed', 250000, now()->startOfMonth()->addDay());
        $this->createBooking($court, $venue, 'pending_verification', 300000, now()->startOfMonth()->addDays(2));
        $this->createBooking($court, $venue, 'confirmed', 200000, now()->subMonth());

        $view = app(FinancialReportController::class)->index(Request::create('/financial-reports', 'GET', [
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]));

        $this->assertSame(250000, $view->getData()['summary']['income']);
        $this->assertSame(1, $view->getData()['summary']['transactions']);
        $this->assertSame(250000, $view->getData()['summary']['average']);
        $this->assertCount(1, $view->getData()['transactions']);
        $this->assertSame('Venue Rekap', $view->getData()['venueSummaries']->first()->venue_name);
    }

    private function createBooking(Court $court, Venue $venue, string $status, int $amount, ?Carbon $verifiedAt): Booking
    {
        return Booking::create([
            'court_id' => $court->id,
            'venue_id' => $venue->id,
            'customer_name' => 'Pelanggan Rekap',
            'customer_phone' => '08123456789',
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '07:00',
            'amount' => $amount,
            'status' => $status,
            'payment_expires_at' => now()->addMinutes(30),
            'verified_at' => $verifiedAt,
        ]);
    }
}
