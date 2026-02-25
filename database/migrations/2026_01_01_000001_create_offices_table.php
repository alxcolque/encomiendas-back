<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('address');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('coordinates', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
