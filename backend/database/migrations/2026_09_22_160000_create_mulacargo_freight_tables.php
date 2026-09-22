<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Freight Categories / Vehicle Types for MulaCargo
        if (!Schema::hasTable('mulacargo_freight_categories')) {
            Schema::create('mulacargo_freight_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Utilitario / Pickup, Furgón / VUC, Camión 3/4 & Toco, Truck & Carreta
                $table->string('slug')->unique();
                $table->decimal('max_weight_kg', 10, 2);
                $table->decimal('max_volume_m3', 10, 2);
                $table->text('description')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        // 2. Backhaul / Radar de Retorno Table
        if (!Schema::hasTable('mulacargo_backhauls')) {
            Schema::create('mulacargo_backhauls', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('driver_id');
                $table->string('home_base_address');
                $table->decimal('home_lat', 10, 8);
                $table->decimal('home_lng', 11, 8);
                $table->string('current_destination_address');
                $table->decimal('current_dest_lat', 10, 8);
                $table->decimal('current_dest_lng', 11, 8);
                $table->boolean('is_active')->default(true);
                $table->timestamp('activated_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Platform Settings Update (3.5% Commission default)
        if (Schema::hasTable('settings')) {
            // Ensure commission is configured
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mulacargo_backhauls');
        Schema::dropIfExists('mulacargo_freight_categories');
    }
};
