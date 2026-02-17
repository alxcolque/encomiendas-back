<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // ej: 'delivery', 'express'
            $table->string('client_name',100);
            $table->string('pickup');            // Dirección de recogida
            $table->string('delivery');          // Dirección de entrega
            $table->text('address')->nullable(); // Detalles extra
            $table->decimal('delivery_fee', 10, 2);
            $table->enum('urgency', ['baja', 'media', 'alta'])->default('baja');
            $table->text('description')->nullable();
            $table->string('currency', 3)->default('BOB');
            $table->string('status', 30)->default('pending'); //['pending', 'assigned', 'picked_up', 'delivered', 'cancelled']
            $table->string('duration', 30)->nullable(); // Tiempo estimado en mins
            $table->integer('points')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
