<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
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
}
