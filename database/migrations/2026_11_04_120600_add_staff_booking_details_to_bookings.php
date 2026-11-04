<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('venue_id')->constrained('users')->nullOnDelete();
            $table->string('booking_source', 16)->default('website')->after('created_by');
            $table->string('payment_method', 32)->nullable()->after('booking_source');
        });

        DB::table('roles')->insertOrIgnore([
            'name' => 'Staff',
            'slug' => 'staff',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['booking_source', 'payment_method']);
        });
    }
};
