<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number', 50)->unique()->nullable();
            $table->string('nit_ci', 20);
            $table->string('business_name');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 50)->nullable();
            $table->enum('status', ['paid', 'pending', 'cancelled'])->default('paid');
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
