<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('site_name')->default('Kolmox');
            $table->text('site_description')->nullable();
            $table->text('keywords')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->longText('terms_and_conditions')->nullable();
            $table->longText('privacy_policy')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
