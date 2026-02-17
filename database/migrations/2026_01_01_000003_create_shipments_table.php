<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code', 50)->unique();
            $table->unsignedBigInteger('origin_office_id')->nullable();
            $table->unsignedBigInteger('destination_office_id')->nullable();
            $table->string('sender_name');
            $table->string('sender_phone', 20)->nullable();
            $table->string('receiver_name');
            $table->string('receiver_phone', 20)->nullable();
            $table->enum('current_status', ['created', 'in_transit', 'at_office', 'out_for_delivery', 'delivered', 'cancelled'])->default('created');
            $table->dateTime('estimated_delivery')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('origin_office_id')->references('id')->on('offices')->onDelete('set null');
            $table->foreign('destination_office_id')->references('id')->on('offices')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
