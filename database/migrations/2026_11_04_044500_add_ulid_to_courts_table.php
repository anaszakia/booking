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
        Schema::table('courts', function (Blueprint $table) {
            $table->string('ulid', 26)->nullable()->after('id')->unique();
        });

        DB::table('courts')
            ->whereNull('ulid')
            ->orderBy('id')
            ->chunkById(100, function ($courts): void {
                foreach ($courts as $court) {
                    DB::table('courts')
                        ->where('id', $court->id)
                        ->update(['ulid' => (string) new Ulid]);
                }
            });

        Schema::table('courts', function (Blueprint $table) {
            $table->string('ulid', 26)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('courts', function (Blueprint $table) {
            $table->dropUnique(['ulid']);
            $table->dropColumn('ulid');
        });
    }
};
