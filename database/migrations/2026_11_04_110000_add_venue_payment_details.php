<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->string('qris_path')->nullable()->after('logo_path');
        });

        Schema::create('venue_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->string('bank_name', 100);
            $table->string('account_name', 120);
            $table->string('account_number', 64);
            $table->timestamps();

            $table->index(['venue_id', 'bank_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_bank_accounts');

        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn('qris_path');
        });
    }
};
