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
            $table->unsignedBigInteger('origin_office_id');
            $table->unsignedBigInteger('destination_office_id');
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->tinyInteger('tracking_pay')->default(1); //1=sender, 2=receiver, 3=both
            $table->text('observation')->nullable();
            $table->boolean('is_pack')->default(true); //false=sobre, true=paquete
            $table->decimal('weight', 10, 2)->nullable();
            $table->boolean('is_fragile')->default(false); //false=no, true=yes
            $table->enum('type_service', ['normal', 'standard', 'express'])->default('normal');
            $table->tinyInteger('track_type')->default(1); //1=terrestre, 2=aereo
            $table->string('current_status', 30)->default('created');
            $table->dateTime('estimated_delivery')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('origin_office_id')->references('id')->on('offices')->onDelete('cascade');
            $table->foreign('destination_office_id')->references('id')->on('offices')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
