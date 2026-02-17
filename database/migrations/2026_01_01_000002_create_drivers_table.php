<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('vehicle_type', 50)->nullable();
            $table->string('plate_number', 20)->nullable();
            $table->string('license_number', 50)->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->integer('total_deliveries')->default(0);
            $table->enum('status', ['active', 'inactive', 'on-delivery'])->default('active');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
