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

        // 2. Backhaul / Radar de Retorno Table (Supports Live GPS & Pre-Trip Scheduling)
        if (!Schema::hasTable('mulacargo_backhauls')) {
            Schema::create('mulacargo_backhauls', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('driver_id')->index();
                $table->enum('trip_type', ['live_return', 'pre_scheduled_trip'])->default('live_return');
                $table->string('origin_address')->nullable();
                $table->decimal('origin_lat', 10, 8)->nullable();
                $table->decimal('origin_lng', 11, 8)->nullable();
                $table->string('destination_address');
                $table->decimal('dest_lat', 10, 8);
                $table->decimal('dest_lng', 11, 8);
                $table->string('home_base_address')->nullable();
                $table->decimal('home_lat', 10, 8)->nullable();
                $table->decimal('home_lng', 11, 8)->nullable();
                $table->dateTime('planned_outbound_at')->nullable();
                $table->dateTime('estimated_arrival_at')->nullable();
                $table->dateTime('planned_return_at')->nullable(); // When driver is empty and ready for return cargo
                $table->integer('max_corridor_deviation_km')->default(20);
                $table->enum('status', ['scheduled', 'in_outbound', 'at_destination', 'in_return', 'completed', 'cancelled'])->default('scheduled');
                $table->boolean('is_active')->default(true);
                $table->timestamp('activated_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mulacargo_backhauls');
        Schema::dropIfExists('mulacargo_freight_categories');
    }
};
