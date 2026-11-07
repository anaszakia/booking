<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Services\MinioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    private const BOOKING_SLOTS = ['07:00', '09:00', '11:00', '13:00', '15:00', '17:00', '19:00', '21:00'];

    public function __construct(private readonly MinioService $minio) {}

    public function availability(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);
        if (now()->startOfDay()->diffInDays($data['from']) > 365 || now()->startOfDay()->diffInDays($data['to']) > 365) {
            throw ValidationException::withMessages(['from' => 'Jadwal hanya dapat dilihat untuk 1 tahun ke depan.']);
        }
        if (now()->parse($data['from'])->diffInDays(now()->parse($data['to'])) > 42) {
            throw ValidationException::withMessages(['to' => 'Rentang jadwal maksimal 42 hari.']);
        }

        $this->expireUnpaidBookings();
        $bookings = Booking::query()
            ->with('court:id,ulid,name')
            ->blocksSlot()
            ->whereDate('booking_date', '>=', $data['from'])
            ->whereDate('booking_date', '<=', $data['to'])
            ->orderBy('booking_date')
            ->orderBy('booking_time')
            ->get(['court_id', 'booking_date', 'booking_time', 'status'])
            ->map(fn (Booking $booking) => [
                'court' => $booking->court->ulid,
                'courtName' => $booking->court->name,
                'date' => $booking->booking_date->toDateString(),
                'time' => substr($booking->booking_time, 0, 5),
                'status' => $booking->status,
            ])
            ->values();

        return response()->json(['bookings' => $bookings]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'court_ulid' => ['required', 'string', 'size:26'],
            'booking_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'booking_time' => ['required', 'in:'.implode(',', self::BOOKING_SLOTS)],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:254'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        if (now()->parse($data['booking_date'].' '.$data['booking_time'])->lte(now())) {
            throw ValidationException::withMessages(['booking_time' => 'Jam booking harus masih akan datang. Silakan pilih slot lain.']);
        }

        $booking = $this->createBooking($data);

        return redirect()->route('booking.payment', $booking)->with('success', 'Booking dibuat. Selesaikan pembayaran dalam 30 menit agar slot tidak dilepas.');
    }

    public function createStaffBooking(): View
    {
        $courts = Court::query()
            ->active()
            ->whereHas('venue', fn ($query) => $query->where('is_active', true))
            ->with('venue:id,ulid,name,address,qris_path')
            ->orderBy('name')
            ->get();

        return view('admin.bookings.create', compact('courts'));
    }

    public function storeStaffBooking(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'court_ulid' => ['required', 'string', 'size:26'],
            'booking_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'booking_time' => ['required', 'in:'.implode(',', self::BOOKING_SLOTS)],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:254'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_option' => ['required', 'in:online,onsite'],
            'payment_method' => ['required_if:payment_option,onsite', 'nullable', 'in:cash,qris,bank_transfer'],
        ]);
        if (now()->parse($data['booking_date'].' '.$data['booking_time'])->lte(now())) {
            throw ValidationException::withMessages(['booking_time' => 'Jam booking harus masih akan datang. Silakan pilih slot lain.']);
        }

        $booking = $this->createBooking(
            $data,
            (int) session('user_id'),
            'staff',
            $data['payment_option'] === 'onsite' ? $data['payment_method'] : null,
        );

        if ($data['payment_option'] === 'onsite') {
            return redirect()->route('bookings.index')->with('success', "Booking {$booking->booking_number} dibuat dan dikonfirmasi sebagai pembayaran di lokasi.");
        }

        return redirect()->route('booking.payment', $booking)->with('success', 'Booking dibuat. Pelanggan harus menyelesaikan pembayaran online dalam 30 menit.');
    }

    private function createBooking(array $data, ?int $createdBy = null, string $source = 'website', ?string $paymentMethod = null): Booking
    {
        return DB::transaction(function () use ($data, $createdBy, $source, $paymentMethod): Booking {
            $court = Court::query()
                ->with(['venue.bankAccounts'])
                ->where('ulid', $data['court_ulid'])
                ->lockForUpdate()
                ->firstOrFail();

            if (! $court->is_active || ! $court->venue?->is_active) {
                throw ValidationException::withMessages(['court_ulid' => 'Lapangan atau venue ini tidak aktif. Silakan pilih lapangan lain.']);
            }
            $isOnsitePayment = ($data['payment_option'] ?? null) === 'onsite';
            if (! $isOnsitePayment && ! $court->venue->qris_path && $court->venue->bankAccounts->isEmpty()) {
                throw ValidationException::withMessages(['court_ulid' => 'Metode pembayaran untuk venue ini belum tersedia. Silakan hubungi admin.']);
            }

            $now = now();
            if ($now->parse($data['booking_date'].' '.$data['booking_time'])->lte($now)) {
                throw ValidationException::withMessages(['booking_time' => 'Jam booking harus masih akan datang. Silakan pilih slot lain.']);
            }
            Booking::query()
                ->where('court_id', $court->id)
                ->where('booking_date', $data['booking_date'])
                ->where('booking_time', $data['booking_time'])
                ->where('status', 'pending_payment')
                ->where('payment_expires_at', '<=', $now)
                ->update(['status' => 'expired']);

            $alreadyBooked = Booking::query()
                ->where('court_id', $court->id)
                ->where('booking_date', $data['booking_date'])
                ->where('booking_time', $data['booking_time'])
                ->blocksSlot()
                ->exists();
            if ($alreadyBooked) {
                throw ValidationException::withMessages(['booking_time' => 'Slot ini baru saja dipesan. Silakan pilih jam lain.']);
            }

            return Booking::create([
                'court_id' => $court->id,
                'venue_id' => $court->venue_id,
                'created_by' => $createdBy,
                'booking_source' => $source,
                'payment_method' => $paymentMethod,
                'customer_name' => $data['name'],
                'customer_phone' => $data['phone'],
                'customer_email' => $data['email'] ?? null,
                'notes' => $data['notes'] ?? null,
                'booking_date' => $data['booking_date'],
                'booking_time' => $data['booking_time'],
                'amount' => $court->price_per_hour,
                'status' => $isOnsitePayment ? 'confirmed' : 'pending_payment',
                'payment_expires_at' => $isOnsitePayment ? null : $now->copy()->addMinutes(30),
                'verified_at' => $isOnsitePayment ? $now : null,
            ]);
        });
    }

    public function payment(Booking $booking): View
    {
        if ($booking->status === 'pending_payment' && $booking->payment_expires_at->lte(now())) {
            $booking->update(['status' => 'expired']);
        }
        $booking->load(['court', 'venue.bankAccounts']);

        return view('booking.payment', compact('booking'));
    }

    public function uploadForm(): View
    {
        $primaryVenue = Venue::query()->active()->orderBy('name')->first();

        return view('booking.upload-proof', compact('primaryVenue'));
    }

    public function uploadProof(Request $request): RedirectResponse
    {
        $request->merge(['booking_number' => strtoupper(trim((string) $request->input('booking_number')))]);
        $data = $request->validate([
            'booking_number' => ['required', 'regex:/^BOOK-\d{6}-\d{4}$/'],
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);
        $booking = Booking::query()->where('booking_number', $data['booking_number'])->first();

        if (! $booking) {
            throw ValidationException::withMessages(['booking_number' => 'Nomor booking tidak ditemukan. Periksa kembali nomor booking Anda.']);
        }

        if ($booking->status !== 'pending_payment') {
            throw ValidationException::withMessages(['booking_number' => 'Booking ini tidak sedang menunggu bukti pembayaran.']);
        }
        if ($booking->payment_expires_at->isPast()) {
            $booking->update(['status' => 'expired']);
            throw ValidationException::withMessages(['booking_number' => 'Batas pembayaran sudah habis dan slot booking telah dilepas. Silakan buat booking baru.']);
        }

        $proofDisk = $this->minio->privateDiskName();
        $proofPath = $this->minio->uploadPrivate($request->file('payment_proof'), $this->minio->buildPath('booking-payment-proofs'));
        $uploadResult = DB::transaction(function () use ($booking, $proofPath, $proofDisk): string {
            $lockedBooking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($lockedBooking->status !== 'pending_payment') {
                return 'processed';
            }
            if ($lockedBooking->payment_expires_at->isPast()) {
                if ($lockedBooking->status === 'pending_payment') {
                    $lockedBooking->update(['status' => 'expired']);
                }

                return 'expired';
            }

            $lockedBooking->update([
                'payment_proof_path' => $proofPath,
                'payment_proof_disk' => $proofDisk,
                'payment_uploaded_at' => now(),
                'status' => 'pending_verification',
            ]);

            return 'accepted';
        });

        if ($uploadResult !== 'accepted') {
            if (! Storage::disk($proofDisk)->delete($proofPath)) {
                Log::warning('Gagal menghapus bukti bayar yang tidak diterima.', ['booking_ulid' => $booking->ulid, 'file_path' => $proofPath]);
            }

            $message = $uploadResult === 'expired'
                ? 'Batas pembayaran booking sudah habis. Silakan buat booking baru.'
                : 'Booking ini sudah menerima bukti pembayaran atau telah diproses.';
            throw ValidationException::withMessages(['booking_number' => $message]);
        }

        return redirect()->route('booking.payment', $booking)->with('success', 'Bukti pembayaran berhasil diunggah dan menunggu verifikasi admin.');
    }

    public function index(): View
    {
        $this->expireUnpaidBookings();
        $bookings = Booking::query()
            ->with(['court:id,name,ulid', 'venue:id,name', 'createdBy:id,name'])
            ->latest()
            ->paginate(20);
        $isAdmin = User::query()->find(session('user_id'))?->hasRole('superadmin') ?? false;

        return view('admin.bookings.index', compact('bookings', 'isAdmin'));
    }

    public function calendar(Request $request): View
    {
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);
        $month = Carbon::createFromFormat('Y-m', $filters['month'] ?? now()->format('Y-m'))->startOfMonth();

        $this->expireUnpaidBookings();
        $bookings = Booking::query()
            ->with(['court:id,name,ulid', 'venue:id,name', 'createdBy:id,name'])
            ->whereBetween('booking_date', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->orderBy('booking_date')
            ->orderBy('booking_time')
            ->get();

        $bookingsByDate = $bookings->groupBy(fn (Booking $booking) => $booking->booking_date->toDateString());
        $calendarStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $previousMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');
        $monthLabel = $month->locale('id')->translatedFormat('F Y');
        $calendarEvents = $bookings->map(fn (Booking $booking) => [
            'date' => $booking->booking_date->toDateString(),
            'time' => substr($booking->booking_time, 0, 5),
            'customer' => $booking->customer_name,
            'number' => $booking->booking_number,
            'court' => $booking->court->name,
            'venue' => $booking->venue->name,
            'status' => $booking->status,
            'source' => $booking->booking_source,
            'paymentMethod' => $booking->payment_method,
            'amount' => $booking->amount,
        ])->values();

        return view('admin.bookings.calendar', compact(
            'month',
            'monthLabel',
            'bookings',
            'calendarEvents',
            'bookingsByDate',
            'calendarStart',
            'calendarEnd',
            'previousMonth',
            'nextMonth',
        ));
    }

    public function proof(Booking $booking): StreamedResponse
    {
        abort_unless($booking->payment_proof_path, 404);

        return $this->minio->previewPrivate($booking->payment_proof_path, $booking->payment_proof_disk);
    }

    public function verify(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate(['decision' => ['required', 'in:approve,reject']]);
        $updated = DB::transaction(function () use ($booking, $data): bool {
            $lockedBooking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($lockedBooking->status !== 'pending_verification') {
                return false;
            }

            $lockedBooking->update([
                'status' => $data['decision'] === 'approve' ? 'confirmed' : 'rejected',
                'verified_at' => now(),
            ]);

            return true;
        });

        if (! $updated) {
            return back()->with('error', 'Booking ini sudah diproses atau tidak menunggu verifikasi.');
        }

        return back()->with('success', $data['decision'] === 'approve'
            ? 'Pembayaran diverifikasi dan booking dikonfirmasi.'
            : 'Bukti pembayaran ditolak. Slot tersedia kembali untuk booking.');
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        $cancelled = DB::transaction(function () use ($booking): bool {
            $lockedBooking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if (! in_array($lockedBooking->status, Booking::ACTIVE_STATUSES, true)) {
                return false;
            }

            $lockedBooking->update(['status' => 'cancelled']);

            return true;
        });

        if (! $cancelled) {
            return back()->with('error', 'Booking ini sudah dibatalkan atau tidak dapat dibatalkan lagi.');
        }

        return back()->with('success', "Booking {$booking->booking_number} berhasil dibatalkan. Slot tersedia kembali.");
    }

    private function expireUnpaidBookings(): void
    {
        Booking::query()
            ->where('status', 'pending_payment')
            ->where('payment_expires_at', '<=', now())
            ->update(['status' => 'expired']);
    }
}
