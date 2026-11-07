<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Role;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_summarizes_today_bookings_and_pending_payment_proofs(): void
    {
        [$venue, $court] = $this->makeCourt();
        $today = now()->toDateString();
        $this->makeBooking($venue, $court, $today, '09:00', 'confirmed', 2, null);
        $this->makeBooking($venue, $court, $today, '13:00', 'pending_verification', 1, 'proofs/payment.png');
        $this->makeBooking($venue, $court, now()->subDay()->toDateString(), '09:00', 'confirmed', 1, null);
        $admin = $this->makeUserWithRole('superadmin');

        $this->withSession(['user_id' => $admin->id])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Booking aktif hari ini')
            ->assertSee('>2</div>', false)
            ->assertSee('3 <span class="fs-5 fw-medium">jam</span>', false)
            ->assertSee('Rp750.000')
            ->assertSee('Bukti pembayaran menunggu pemeriksaan')
            ->assertSee('Lihat bukti')
            ->assertSee('Jadwal booking hari ini')
            ->assertSee('Lapangan Dashboard');
    }

    public function test_staff_dashboard_shows_today_schedule_without_admin_proof_review_controls(): void
    {
        [$venue, $court] = $this->makeCourt();
        $this->makeBooking($venue, $court, now()->toDateString(), '10:00', 'confirmed', 1, null);
        $staff = $this->makeUserWithRole('staff');

        $this->withSession(['user_id' => $staff->id])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Jadwal booking hari ini')
            ->assertSee('Pelanggan Dashboard')
            ->assertDontSee('Bukti pembayaran menunggu pemeriksaan')
            ->assertDontSee('Lihat bukti');
    }

    private function makeCourt(): array
    {
        $venue = Venue::create([
            'name' => 'Venue Dashboard',
            'address' => 'Jakarta',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'qris_path' => 'venues/qris.png',
            'is_active' => true,
        ]);
        $court = Court::create([
            'name' => 'Lapangan Dashboard',
            'venue_id' => $venue->id,
            'court_type' => 'indoor',
            'price_per_hour' => 250000,
            'is_active' => true,
        ]);

        return [$venue, $court];
    }

    private function makeBooking(Venue $venue, Court $court, string $date, string $time, string $status, int $duration, ?string $proofPath): Booking
    {
        return Booking::create([
            'court_id' => $court->id,
            'venue_id' => $venue->id,
            'customer_name' => 'Pelanggan Dashboard',
            'customer_phone' => '08123456789',
            'booking_date' => $date,
            'booking_time' => $time,
            'duration_hours' => $duration,
            'amount' => $court->price_per_hour * $duration,
            'status' => $status,
            'payment_proof_path' => $proofPath,
            'payment_uploaded_at' => $proofPath ? now() : null,
        ]);
    }

    private function makeUserWithRole(string $slug): User
    {
        $role = Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);
        $user = User::create([
            'name' => ucfirst($slug).' Dashboard',
            'email' => $slug.'-dashboard@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->attach($role);

        return $user;
    }
}
