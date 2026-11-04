<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('court_id')->constrained()->restrictOnDelete();
            $table->foreignId('venue_id')->constrained()->restrictOnDelete();
            $table->string('customer_name', 120);
            $table->string('customer_phone', 32);
            $table->string('customer_email', 254)->nullable();
            $table->text('notes')->nullable();
            $table->date('booking_date');
            $table->time('booking_time');
            $table->unsignedInteger('amount');
            $table->string('status', 32)->default('pending_payment');
            $table->timestamp('payment_expires_at')->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->timestamp('payment_uploaded_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['court_id', 'booking_date', 'booking_time', 'status']);
            $table->index(['status', 'payment_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
