<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courts', function (Blueprint $table) {
            $table->foreignId('venue_id')->nullable()->after('ulid')->constrained()->restrictOnDelete();
        });

        $venueId = DB::table('venues')->where('is_active', true)->orderBy('name')->value('id');
        if ($venueId) {
            DB::table('courts')->whereNull('venue_id')->update(['venue_id' => $venueId]);
        }
    }

    public function down(): void
    {
        Schema::table('courts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('venue_id');
        });
    }
};
