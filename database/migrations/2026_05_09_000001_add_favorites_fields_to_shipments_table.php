<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->boolean('is_favorite')->default(false);
            $table->decimal('amount_fav', 10, 2)->nullable()->after('discount');
            $table->string('product_content_fav', 255)->nullable()->after('amount_fav');
            $table->decimal('percent_fav', 5, 2)->nullable()->after('product_content_fav');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['is_favorite', 'amount_fav', 'product_content_fav', 'percent_fav']);
        });
    }
};
