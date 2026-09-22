<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mulacargo_escrow_payments')) {
            Schema::create('mulacargo_escrow_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('freight_id')->index();
                $table->unsignedBigInteger('shipper_id')->index();
                $table->unsignedBigInteger('carrier_id')->index()->nullable();
                $table->decimal('gross_amount', 10, 2);
                $table->decimal('platform_commission_pct', 5, 2)->default(3.50);
                $table->decimal('platform_commission_amount', 10, 2);
                $table->decimal('net_carrier_amount', 10, 2);
                $table->string('currency')->default('BOB');
                $table->string('qr_code_image_url')->nullable();
                $table->text('qr_emvco_payload')->nullable();
                $table->string('bank_reference')->nullable();
                $table->string('delivery_otp_hash')->nullable();
                $table->enum('status', [
                    'qr_generated',
                    'escrow_funded',
                    'in_transit',
                    'delivered_pending_release',
                    'released_to_carrier',
                    'refunded_to_shipper',
                    'disputed'
                ])->default('qr_generated');
                $table->timestamp('funded_at')->nullable();
                $table->timestamp('released_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mulacargo_escrow_payments');
    }
};
