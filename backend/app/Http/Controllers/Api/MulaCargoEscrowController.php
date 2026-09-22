<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MulaCargoEscrowController extends Controller
{
    /**
     * Generate Bolivian Simple QR for Escrow Freight Deposit.
     */
    public function generateSimpleQr(Request $request)
    {
        $validated = $request->validate([
            'freight_id' => 'required|integer',
            'shipper_id' => 'required|integer',
            'carrier_id' => 'nullable|integer',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
        ]);

        $grossAmount = (float) $validated['amount'];
        $commissionPct = 3.50;
        $commissionAmount = round($grossAmount * ($commissionPct / 100), 2);
        $netCarrierAmount = round($grossAmount - $commissionAmount, 2);

        $bankRef = 'MULA-' . strtoupper(bin2hex(random_bytes(4))) . '-' . $validated['freight_id'];
        $deliveryOtp = (string) random_int(1000, 9999);

        // Generate synthetic EMVCo Simple QR payload for Bolivian Interbank Switch (Asoban / Simple QR)
        $qrPayload = sprintf(
            '00020101021226480010BO.GOB.ASOBAN0112MULA_CARGO_BO520459995303068540%s5802BO5912MULACARGO_SRL6011SANTA_CRUZ62240120%s6304ABCD',
            number_format($grossAmount, 2, '.', ''),
            $bankRef
        );

        $escrowId = DB::table('mulacargo_escrow_payments')->insertGetId([
            'freight_id' => $validated['freight_id'],
            'shipper_id' => $validated['shipper_id'],
            'carrier_id' => $validated['carrier_id'] ?? null,
            'gross_amount' => $grossAmount,
            'platform_commission_pct' => $commissionPct,
            'platform_commission_amount' => $commissionAmount,
            'net_carrier_amount' => $netCarrierAmount,
            'currency' => 'BOB',
            'qr_code_image_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrPayload),
            'qr_emvco_payload' => $qrPayload,
            'bank_reference' => $bankRef,
            'delivery_otp_hash' => password_hash($deliveryOtp, PASSWORD_DEFAULT),
            'status' => 'qr_generated',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'escrow_id' => $escrowId,
            'bank_reference' => $bankRef,
            'qr_code_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrPayload),
            'amount_bob' => $grossAmount,
            'commission_rate' => '3.5%',
            'platform_fee_bob' => $commissionAmount,
            'carrier_payout_bob' => $netCarrierAmount,
            'demo_delivery_otp' => $deliveryOtp, // For staging/testing verification
            'message' => 'QR Simple generado con éxito. Los fondos quedarán custodiados en Escrow hasta la entrega confirmada.',
        ]);
    }

    /**
     * Webhook/Callback to confirm Escrow Deposit.
     */
    public function confirmPayment(Request $request)
    {
        $validated = $request->validate([
            'bank_reference' => 'required|string',
        ]);

        $escrow = DB::table('mulacargo_escrow_payments')
            ->where('bank_reference', $validated['bank_reference'])
            ->first();

        if (!$escrow) {
            return response()->json(['success' => false, 'message' => 'Referencia de pago no encontrada.'], 404);
        }

        DB::table('mulacargo_escrow_payments')
            ->where('id', $escrow->id)
            ->update([
                'status' => 'escrow_funded',
                'funded_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        return response()->json([
            'success' => true,
            'status' => 'escrow_funded',
            'message' => 'Fondos acreditados en Custodia Escrow. El transportista puede iniciar el viaje con garantía total de pago.',
        ]);
    }

    /**
     * Receiver verifies Delivery OTP, releasing funds to carrier wallet.
     */
    public function verifyDeliveryOtp(Request $request)
    {
        $validated = $request->validate([
            'freight_id' => 'required|integer',
            'otp' => 'required|string',
        ]);

        $escrow = DB::table('mulacargo_escrow_payments')
            ->where('freight_id', $validated['freight_id'])
            ->orderBy('id', 'desc')
            ->first();

        if (!$escrow) {
            return response()->json(['success' => false, 'message' => 'Escrow de flete no encontrado.'], 404);
        }

        if (!password_verify($validated['otp'], $escrow->delivery_otp_hash)) {
            return response()->json(['success' => false, 'message' => 'Código OTP de entrega incorrecto.'], 422);
        }

        DB::table('mulacargo_escrow_payments')
            ->where('id', $escrow->id)
            ->update([
                'status' => 'released_to_carrier',
                'released_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        return response()->json([
            'success' => true,
            'status' => 'released_to_carrier',
            'payout_summary' => [
                'gross_freight' => $escrow->gross_amount,
                'mula_commission_3_5_pct' => $escrow->platform_commission_amount,
                'net_paid_to_carrier' => $escrow->net_carrier_amount,
            ],
            'message' => 'Entrega validada exitosamente. Fondos liquidados al transportista con descuento automático de comisión 3.5%.',
        ]);
    }

    /**
     * Get live status of Escrow payment.
     */
    public function getEscrowStatus($freightId)
    {
        $escrow = DB::table('mulacargo_escrow_payments')
            ->where('freight_id', $freightId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$escrow) {
            return response()->json(['success' => false, 'message' => 'Escrow no encontrado.'], 404);
        }

        return response()->json([
            'success' => true,
            'escrow' => $escrow,
        ]);
    }
}
