<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_a')->constrained('cities')->cascadeOnDelete();
            $table->foreignId('city_b')->constrained('cities')->cascadeOnDelete();
            $table->decimal('value', 10, 2);
            $table->timestamps();

            $table->unique(['city_a', 'city_b']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_values');
    }
};
