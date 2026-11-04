<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Role;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_has_a_daily_booking_number_and_is_reserved_on_the_server(): void
    {
        [$venue, $court] = $this->makeBookableCourt();
        $bookingDate = now()->addDay()->toDateString();

        $response = $this->post(route('booking.store'), [
            'court_ulid' => $court->ulid,
            'booking_date' => $bookingDate,
            'booking_time' => '07:00',
            'name' => 'Pelanggan Uji',
            'phone' => '08123456789',
            'email' => 'pelanggan@example.com',
        ]);

        $booking = Booking::query()->firstOrFail();
        $response->assertRedirect(route('booking.payment', $booking));
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $booking->ulid);
        $this->assertSame('BOOK-'.now()->format('dmy').'-0001', $booking->booking_number);
        $this->assertSame('pending_payment', $booking->status);
        $this->assertSame(250000, $booking->amount);
        $this->assertSame('website', $booking->booking_source);

        $secondBooking = Booking::create([
            'court_id' => $court->id,
            'venue_id' => $venue->id,
            'customer_name' => 'Pelanggan Kedua',
            'customer_phone' => '08123456789',
            'booking_date' => $bookingDate,
            'booking_time' => '09:00',
            'amount' => $court->price_per_hour,
            'status' => 'pending_payment',
            'payment_expires_at' => now()->addMinutes(30),
        ]);
        $this->assertSame('BOOK-'.now()->format('dmy').'-0002', $secondBooking->booking_number);

        $this->get(route('booking.availability', ['from' => $bookingDate, 'to' => $bookingDate]))
            ->assertOk()
            ->assertJsonFragment(['court' => $court->ulid, 'date' => $bookingDate, 'time' => '07:00']);

        $this->from(route('home'))->post(route('booking.store'), [
            'court_ulid' => $court->ulid,
            'booking_date' => $bookingDate,
            'booking_time' => '07:00',
            'name' => 'Pelanggan Lain',
            'phone' => '08987654321',
        ])->assertSessionHasErrors('booking_time');
    }

    public function test_staff_can_create_an_onsite_paid_booking_and_confirm_it(): void
    {
        [, $court] = $this->makeBookableCourt();
        $staff = $this->makeStaffUser();

        $this->withSession(['user_id' => $staff->id])
            ->post(route('bookings.store'), [
                'court_ulid' => $court->ulid,
                'booking_date' => now()->addDay()->toDateString(),
                'booking_time' => '07:00',
                'name' => 'Pelanggan di Venue',
                'phone' => '08123456789',
                'payment_option' => 'onsite',
                'payment_method' => 'cash',
            ])
            ->assertRedirect(route('bookings.index'));

        $booking = Booking::query()->firstOrFail();
        $this->assertSame('staff', $booking->booking_source);
        $this->assertSame($staff->id, $booking->created_by);
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('cash', $booking->payment_method);
        $this->assertNotNull($booking->verified_at);
        $this->assertNull($booking->payment_expires_at);
    }

    public function test_staff_can_create_an_online_booking_which_keeps_the_payment_deadline(): void
    {
        [, $court] = $this->makeBookableCourt();
        $staff = $this->makeStaffUser();

        $response = $this->withSession(['user_id' => $staff->id])
            ->post(route('bookings.store'), [
                'court_ulid' => $court->ulid,
                'booking_date' => now()->addDay()->toDateString(),
                'booking_time' => '09:00',
                'name' => 'Pelanggan Online',
                'phone' => '08123456789',
                'payment_option' => 'online',
            ]);

        $booking = Booking::query()->firstOrFail();
        $response->assertRedirect(route('booking.payment', $booking));
        $this->assertSame('staff', $booking->booking_source);
        $this->assertSame('pending_payment', $booking->status);
        $this->assertEqualsWithDelta(
            now()->addMinutes(30)->timestamp,
            $booking->payment_expires_at->timestamp,
            2,
        );
        $this->assertNull($booking->verified_at);
    }

    public function test_staff_cannot_confirm_a_slot_that_was_already_booked(): void
    {
        [$venue, $court] = $this->makeBookableCourt();
        $bookingDate = now()->addDay()->toDateString();
        Booking::create([
            'court_id' => $court->id,
            'venue_id' => $venue->id,
            'customer_name' => 'Pelanggan Sebelumnya',
            'customer_phone' => '08123456789',
            'booking_date' => $bookingDate,
            'booking_time' => '11:00',
            'amount' => $court->price_per_hour,
            'status' => 'confirmed',
            'verified_at' => now(),
        ]);
        $staff = $this->makeStaffUser();

        $this->from(route('bookings.create'))
            ->withSession(['user_id' => $staff->id])
            ->post(route('bookings.store'), [
                'court_ulid' => $court->ulid,
                'booking_date' => $bookingDate,
                'booking_time' => '11:00',
                'name' => 'Pelanggan Baru',
                'phone' => '08123456789',
                'payment_option' => 'onsite',
                'payment_method' => 'qris',
            ])
            ->assertSessionHasErrors('booking_time');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_staff_can_view_bookings_but_cannot_verify_payments(): void
    {
        [$venue, $court] = $this->makeBookableCourt();
        $booking = Booking::create([
            'court_id' => $court->id,
            'venue_id' => $venue->id,
            'customer_name' => 'Pelanggan Uji',
            'customer_phone' => '08123456789',
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '13:00',
            'amount' => $court->price_per_hour,
            'status' => 'pending_verification',
        ]);
        $staff = $this->makeStaffUser();

        $this->withSession(['user_id' => $staff->id])
            ->get(route('bookings.index'))
            ->assertOk()
            ->assertSee($booking->booking_number)
            ->assertDontSee('Setujui');

        $this->withSession(['user_id' => $staff->id])
            ->post(route('bookings.verify', [$booking, 'decision' => 'approve']))
            ->assertForbidden();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'pending_verification']);
    }

    public function test_admin_calendar_lists_bookings_including_confirmed_rejected_and_cancelled_statuses(): void
    {
        [$venue, $court] = $this->makeBookableCourt();
        $bookingDate = now()->addDay()->toDateString();
        foreach ([
            ['confirmed', 'Pelanggan Disetujui', '09:00'],
            ['rejected', 'Pelanggan Ditolak', '11:00'],
            ['cancelled', 'Pelanggan Dibatalkan', '13:00'],
        ] as [$status, $customerName, $time]) {
            Booking::create([
                'court_id' => $court->id,
                'venue_id' => $venue->id,
                'customer_name' => $customerName,
                'customer_phone' => '08123456789',
                'booking_date' => $bookingDate,
                'booking_time' => $time,
                'amount' => $court->price_per_hour,
                'status' => $status,
            ]);
        }
        $admin = $this->makeUserWithRole('superadmin');

        $this->withSession(['user_id' => $admin->id])
            ->get(route('bookings.calendar', ['month' => now()->addDay()->format('Y-m')]))
            ->assertOk()
            ->assertSee('Pelanggan Disetujui')
            ->assertSee('Pelanggan Ditolak')
            ->assertSee('Pelanggan Dibatalkan')
            ->assertSee('Disetujui')
            ->assertSee('Ditolak')
            ->assertSee('Dibatalkan');
    }

    public function test_admin_can_cancel_a_booking_and_release_its_slot(): void
    {
        [$venue, $court] = $this->makeBookableCourt();
        $bookingDate = now()->addDay()->toDateString();
        $booking = Booking::create([
            'court_id' => $court->id,
            'venue_id' => $venue->id,
            'customer_name' => 'Pelanggan Uji',
            'customer_phone' => '08123456789',
            'booking_date' => $bookingDate,
            'booking_time' => '15:00',
            'amount' => $court->price_per_hour,
            'status' => 'confirmed',
            'verified_at' => now(),
        ]);
        $admin = $this->makeUserWithRole('superadmin');

        $this->from(route('bookings.index'))
            ->withSession(['user_id' => $admin->id])
            ->post(route('bookings.cancel', $booking))
            ->assertRedirect(route('bookings.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
        $this->get(route('booking.availability', ['from' => $bookingDate, 'to' => $bookingDate]))
            ->assertOk()
            ->assertJsonCount(0, 'bookings');
    }

    public function test_staff_cannot_cancel_a_booking(): void
    {
        [$venue, $court] = $this->makeBookableCourt();
        $booking = Booking::create([
            'court_id' => $court->id,
            'venue_id' => $venue->id,
            'customer_name' => 'Pelanggan Uji',
            'customer_phone' => '08123456789',
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '17:00',
            'amount' => $court->price_per_hour,
            'status' => 'confirmed',
        ]);
        $staff = $this->makeStaffUser();

        $this->withSession(['user_id' => $staff->id])
            ->post(route('bookings.cancel', $booking))
            ->assertForbidden();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'confirmed']);
    }

    public function test_payment_proof_upload_moves_booking_to_admin_verification(): void
    {
        config(['filesystems.minio_active' => false]);
        Storage::fake('local');
        [$venue, $court] = $this->makeBookableCourt();
        $booking = Booking::create([
            'court_id' => $court->id,
            'venue_id' => $venue->id,
            'customer_name' => 'Pelanggan Uji',
            'customer_phone' => '08123456789',
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '09:00',
            'amount' => $court->price_per_hour,
            'status' => 'pending_payment',
            'payment_expires_at' => now()->addMinutes(30),
        ]);

        $this->post(route('booking.upload-proof'), [
            'booking_number' => strtolower($booking->booking_number),
            'payment_proof' => UploadedFile::fake()->image('proof.png'),
        ])->assertRedirect(route('booking.payment', $booking));

        $booking->refresh();
        $this->assertSame('pending_verification', $booking->status);
        $this->assertSame('local', $booking->payment_proof_disk);
        Storage::disk('local')->assertExists($booking->payment_proof_path);
    }

    public function test_expired_unpaid_booking_is_released_from_availability(): void
    {
        [$venue, $court] = $this->makeBookableCourt();
        $bookingDate = now()->addDay()->toDateString();
        $booking = Booking::create([
            'court_id' => $court->id,
            'venue_id' => $venue->id,
            'customer_name' => 'Pelanggan Uji',
            'customer_phone' => '08123456789',
            'booking_date' => $bookingDate,
            'booking_time' => '11:00',
            'amount' => $court->price_per_hour,
            'status' => 'pending_payment',
            'payment_expires_at' => now()->subMinute(),
        ]);

        $this->get(route('booking.availability', ['from' => $bookingDate, 'to' => $bookingDate]))
            ->assertOk()
            ->assertJsonCount(0, 'bookings');
        $this->assertDatabaseHas('bookings', ['ulid' => $booking->ulid, 'status' => 'expired']);
    }

    private function makeBookableCourt(): array
    {
        $venue = Venue::create([
            'name' => 'Venue Pembayaran',
            'address' => 'Jakarta',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'qris_path' => 'venues/qris.png',
            'is_active' => true,
        ]);
        $court = Court::create([
            'name' => 'Lapangan Pembayaran',
            'venue_id' => $venue->id,
            'court_type' => 'indoor',
            'price_per_hour' => 250000,
            'is_active' => true,
        ]);

        return [$venue, $court];
    }

    private function makeStaffUser(): User
    {
        return $this->makeUserWithRole('staff');
    }

    private function makeUserWithRole(string $slug): User
    {
        $role = Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);
        $user = User::create([
            'name' => ucfirst($slug).' Test',
            'email' => $slug.'-'.uniqid().'@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->attach($role);

        return $user;
    }
}
