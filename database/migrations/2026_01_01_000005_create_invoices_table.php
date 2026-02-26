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
            $table->string('type', 50)->default('sin'); // sin iva, con iva 
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            /* business */
            $table->string('business_name');
            $table->string('nit_ci_emisor', 20);
            $table->string('invoice_number', 50)->unique()->nullable();
            /* Receptor */
            $table->string('receipt_name');
            $table->string('doc_num', 20);
            $table->string('complement', 5)->nullable();
            /* Code */
            $table->string('cuf')->nullable();
            $table->string('cufd')->nullable();
            $table->integer('cod_suc')->nullable();
            $table->integer('cod_sale')->nullable();
            $table->timestamp('emit_date')->nullable();
            /* Details in json */
            $table->text('details');
            /*FOR DETAILS
            description: Detalle del producto o servicio.
            qty: Cantidad vendida (servicios suelen ser 1).
            unit: según paramétrica (ej. 58 para servicios/unidad).
            unit_price:	Costo por unidad antes de descuentos.
            discount:	Descuento aplicado al ítem.
            sub_total:	(Cantidad * Precio) - Descuento.
            */
            /* Totals fiscals */
            $table->tinyInteger('payment_method')->default(1); // 1 para efectivo, 2 para tarjeta
            $table->decimal('total', 10, 2);
            $table->decimal('total_iva', 10, 2)->default(0);
            $table->string('currency', 50)->default('BOB');
            $table->string('status', 50)->default('paid'); //Ej: Válida, Anulada, Emitida Contingencia
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
