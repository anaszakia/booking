<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('location', 120);
            $table->text('address')->nullable();
            $table->string('court_type', 20)->default('indoor');
            $table->unsignedInteger('price_per_hour');
            $table->text('description')->nullable();
            $table->text('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courts');
    }
};
