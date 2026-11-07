<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->string('hero_eyebrow', 120)->nullable();
            $table->string('hero_title', 120)->nullable();
            $table->string('hero_title_highlight', 120)->nullable();
            $table->text('hero_subtitle')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn(['hero_eyebrow', 'hero_title', 'hero_title_highlight', 'hero_subtitle']);
        });
    }
};
