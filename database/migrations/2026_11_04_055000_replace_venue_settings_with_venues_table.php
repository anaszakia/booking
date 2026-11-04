<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Uid\Ulid;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->string('name', 120);
            $table->string('logo_path')->nullable();
            $table->text('address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'name']);
        });

        if (Schema::hasTable('venue_settings')) {
            foreach (DB::table('venue_settings')->orderBy('id')->get() as $setting) {
                DB::table('venues')->insert([
                    'ulid' => (string) new Ulid,
                    'name' => $setting->name,
                    'logo_path' => null,
                    'address' => $setting->address,
                    'latitude' => $setting->latitude,
                    'longitude' => $setting->longitude,
                    'is_active' => true,
                    'created_at' => $setting->created_at ?? now(),
                    'updated_at' => $setting->updated_at ?? now(),
                ]);
            }

            Schema::drop('venue_settings');
        }
    }

    public function down(): void
    {
        Schema::create('venue_settings', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->string('name', 120)->default('Rally Padel');
            $table->text('address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedTinyInteger('map_zoom')->default(15);
            $table->timestamps();
        });

        $venue = DB::table('venues')->orderBy('id')->first();
        if ($venue) {
            DB::table('venue_settings')->insert([
                'ulid' => $venue->ulid,
                'name' => $venue->name,
                'address' => $venue->address,
                'latitude' => $venue->latitude,
                'longitude' => $venue->longitude,
                'map_zoom' => 14,
                'created_at' => $venue->created_at,
                'updated_at' => $venue->updated_at,
            ]);
        }

        Schema::dropIfExists('venues');
    }
};
