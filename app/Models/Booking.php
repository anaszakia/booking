<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Uid\Ulid;

class Booking extends Model
{
    public const ACTIVE_STATUSES = ['pending_payment', 'pending_verification', 'confirmed'];

    protected $fillable = [
        'court_id',
        'venue_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'notes',
        'booking_date',
        'booking_time',
        'amount',
        'status',
        'payment_expires_at',
        'payment_proof_path',
        'payment_proof_disk',
        'payment_uploaded_at',
        'verified_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking): void {
            $booking->ulid ??= (string) new Ulid;
            $booking->booking_number ??= self::nextBookingNumber();
        });
    }

    private static function nextBookingNumber(): string
    {
        $issuedAt = now();
        $date = $issuedAt->toDateString();

        return DB::transaction(function () use ($date, $issuedAt): string {
            DB::table('booking_number_sequences')->insertOrIgnore([
                'sequence_date' => $date,
                'last_number' => 0,
            ]);

            $sequence = DB::table('booking_number_sequences')
                ->where('sequence_date', $date)
                ->lockForUpdate()
                ->first();

            if ($sequence->last_number >= 9999) {
                throw new RuntimeException("Booking count for {$date} exceeds the 4-digit booking number limit.");
            }

            $nextNumber = $sequence->last_number + 1;
            DB::table('booking_number_sequences')
                ->where('sequence_date', $date)
                ->update(['last_number' => $nextNumber]);

            return sprintf('BOOK-%s-%04d', $issuedAt->format('dmy'), $nextNumber);
        });
    }

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'payment_expires_at' => 'datetime',
            'payment_uploaded_at' => 'datetime',
            'verified_at' => 'datetime',
            'amount' => 'integer',
        ];
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function scopeBlocksSlot(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereIn('status', ['pending_verification', 'confirmed'])
                ->orWhere(function (Builder $query): void {
                    $query->where('status', 'pending_payment')
                        ->where('payment_expires_at', '>', now());
                });
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
