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
        Schema::table('venue_settings', function (Blueprint $table) {
            $table->string('ulid', 26)->nullable()->after('id')->unique();
        });

        Schema::table('social_links', function (Blueprint $table) {
            $table->string('ulid', 26)->nullable()->after('id')->unique();
        });

        foreach (['venue_settings', 'social_links'] as $tableName) {
            DB::table($tableName)
                ->whereNull('ulid')
                ->orderBy('id')
                ->chunkById(100, function ($records) use ($tableName): void {
                    foreach ($records as $record) {
                        DB::table($tableName)
                            ->where('id', $record->id)
                            ->update(['ulid' => (string) new Ulid]);
                    }
                });
        }

        Schema::table('venue_settings', function (Blueprint $table) {
            $table->string('ulid', 26)->nullable(false)->change();
        });

        Schema::table('social_links', function (Blueprint $table) {
            $table->string('ulid', 26)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('social_links', function (Blueprint $table) {
            $table->dropUnique(['ulid']);
            $table->dropColumn('ulid');
        });

        Schema::table('venue_settings', function (Blueprint $table) {
            $table->dropUnique(['ulid']);
            $table->dropColumn('ulid');
        });
    }
};
