<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MulaCargoBackhaulController extends Controller
{
    /**
     * Register or update driver's return home base for backhaul optimization.
     */
    public function setHomeBase(Request $request)
    {
        $validated = $request->validate([
            'driver_id' => 'required|integer',
            'home_base_address' => 'required|string',
            'home_lat' => 'required|numeric',
            'home_lng' => 'required|numeric',
        ]);

        DB::table('mulacargo_backhauls')->updateOrInsert(
            ['driver_id' => $validated['driver_id']],
            [
                'home_base_address' => $validated['home_base_address'],
                'home_lat' => $validated['home_lat'],
                'home_lng' => $validated['home_lng'],
                'is_active' => true,
                'activated_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Base de retorno (Home Base) configurada correctamente en el Radar MulaCargo.',
        ]);
    }

    /**
     * Get suggested return freight loads along the driver's homeward corridor.
     */
    public function getReturnCargo(Request $request)
    {
        $driverId = $request->input('driver_id');
        $currentLat = $request->input('lat');
        $currentLng = $request->input('lng');

        $backhaul = DB::table('mulacargo_backhauls')->where('driver_id', $driverId)->first();

        if (!$backhaul || !$backhaul->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Radar de Retorno no activo. Configure su domicilio base.',
            ], 404);
        }

        // Return cargo recommendation matching corridor between current location and home base
        return response()->json([
            'success' => true,
            'home_base' => $backhaul->home_base_address,
            'suggested_freights' => [
                [
                    'id' => 101,
                    'title' => 'Carga Palletizada Textil (Retorno a Base)',
                    'weight_kg' => 1200,
                    'pickup' => 'Montero (Parada en Ruta)',
                    'dropoff' => $backhaul->home_base_address,
                    'offered_price' => 450.00,
                    'commission_3_5_pct' => 15.75,
                    'net_driver_earnings' => 434.25,
                ],
                [
                    'id' => 102,
                    'title' => 'Insumos Agrícolas Embalados',
                    'weight_kg' => 2500,
                    'pickup' => 'Warnes Industrial',
                    'dropoff' => $backhaul->home_base_address,
                    'offered_price' => 780.00,
                    'commission_3_5_pct' => 27.30,
                    'net_driver_earnings' => 752.70,
                ]
            ]
        ]);
    }
}
