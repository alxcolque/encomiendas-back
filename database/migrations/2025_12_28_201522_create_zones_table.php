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
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Ejemplo: "Zona Central Mercado"
            $table->json('coordinates');                     // Array de puntos [[lat, lng], [lat, lng]...]
            $table->decimal('extra_rate', 8, 2)->default(0); // Recargo en Bs
            $table->string('color')->default('#ff0000');     // Para visualizar en el mapa
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
