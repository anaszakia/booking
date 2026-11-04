<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_number_sequences', function (Blueprint $table) {
            $table->date('sequence_date')->primary();
            $table->unsignedSmallInteger('last_number')->default(0);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_number', 16)->nullable()->unique();
        });

        DB::table('bookings')->orderBy('id')->chunkById(500, function ($bookings): void {
            foreach ($bookings as $booking) {
                $date = Carbon::parse($booking->created_at)->toDateString();
                $lastNumber = (int) (DB::table('booking_number_sequences')
                    ->where('sequence_date', $date)
                    ->value('last_number') ?? 0);

                if ($lastNumber >= 9999) {
                    throw new RuntimeException("Booking count for {$date} exceeds the 4-digit booking number limit.");
                }

                $nextNumber = $lastNumber + 1;
                DB::table('booking_number_sequences')->updateOrInsert(
                    ['sequence_date' => $date],
                    ['last_number' => $nextNumber],
                );
                DB::table('bookings')
                    ->where('id', $booking->id)
                    ->update(['booking_number' => sprintf('BOOK-%s-%04d', Carbon::parse($date)->format('dmy'), $nextNumber)]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['booking_number']);
            $table->dropColumn('booking_number');
        });

        Schema::dropIfExists('booking_number_sequences');
    }
};
