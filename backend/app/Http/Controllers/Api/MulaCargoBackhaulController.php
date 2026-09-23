<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MulaCargoBackhaulController extends Controller
{
    /**
     * Set driver's default home base address / depot.
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
            [
                'driver_id' => $validated['driver_id'],
                'trip_type' => 'live_return',
            ],
            [
                'home_base_address' => $validated['home_base_address'],
                'home_lat' => $validated['home_lat'],
                'home_lng' => $validated['home_lng'],
                'destination_address' => $validated['home_base_address'],
                'dest_lat' => $validated['home_lat'],
                'dest_lng' => $validated['home_lng'],
                'is_active' => true,
                'status' => 'scheduled',
                'activated_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Base de retorno (Home Base) configurada correctamente en el Radar MulaCargo.',
        ]);
    }

    /**
     * Pre-schedule a round trip before leaving the garage/depot.
     * Allows drivers to lock in return cargo before outbound departure.
     */
    public function schedulePreTrip(Request $request)
    {
        $validated = $request->validate([
            'driver_id' => 'required|integer',
            'origin_address' => 'required|string',
            'origin_lat' => 'required|numeric',
            'origin_lng' => 'required|numeric',
            'destination_address' => 'required|string',
            'dest_lat' => 'required|numeric',
            'dest_lng' => 'required|numeric',
            'home_base_address' => 'nullable|string',
            'home_lat' => 'nullable|numeric',
            'home_lng' => 'nullable|numeric',
            'planned_outbound_at' => 'required|date',
            'estimated_arrival_at' => 'nullable|date',
            'planned_return_at' => 'required|date',
            'max_corridor_deviation_km' => 'nullable|integer|min:5|max:100',
        ]);

        $tripId = DB::table('mulacargo_backhauls')->insertGetId([
            'driver_id' => $validated['driver_id'],
            'trip_type' => 'pre_scheduled_trip',
            'origin_address' => $validated['origin_address'],
            'origin_lat' => $validated['origin_lat'],
            'origin_lng' => $validated['origin_lng'],
            'destination_address' => $validated['destination_address'],
            'dest_lat' => $validated['dest_lat'],
            'dest_lng' => $validated['dest_lng'],
            'home_base_address' => $validated['home_base_address'] ?? $validated['origin_address'],
            'home_lat' => $validated['home_lat'] ?? $validated['origin_lat'],
            'home_lng' => $validated['home_lng'] ?? $validated['origin_lng'],
            'planned_outbound_at' => Carbon::parse($validated['planned_outbound_at']),
            'estimated_arrival_at' => isset($validated['estimated_arrival_at']) ? Carbon::parse($validated['estimated_arrival_at']) : null,
            'planned_return_at' => Carbon::parse($validated['planned_return_at']),
            'max_corridor_deviation_km' => $validated['max_corridor_deviation_km'] ?? 20,
            'status' => 'scheduled',
            'is_active' => true,
            'activated_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'trip_id' => $tripId,
            'message' => 'Viaje programado con éxito. El Radar de Retorno está rastreando fletes para su retorno.',
        ]);
    }

    /**
     * Get suggested return freight loads matching the driver's corridor
     * (Supports both live location and pre-scheduled upcoming trips).
     */
    public function getReturnCargo(Request $request)
    {
        $driverId = $request->input('driver_id');
        $tripId = $request->input('trip_id');

        $query = DB::table('mulacargo_backhauls')->where('driver_id', $driverId)->where('is_active', true);
        if ($tripId) {
            $query->where('id', $tripId);
        }
        $trip = $query->orderBy('id', 'desc')->first();

        if (!$trip) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró un viaje o radar activo para este conductor.',
            ], 404);
        }

        $origin = $trip->destination_address; // In backhaul, the return origin is the outbound destination
        $targetDropoff = $trip->home_base_address ?? $trip->origin_address;
        $returnDate = $trip->planned_return_at ? Carbon::parse($trip->planned_return_at)->format('d/m/Y H:i') : 'Inmediato (En Ruta)';

        // Corridor matched simulated freight loads
        $corridorFreights = [
            [
                'id' => 201,
                'title' => 'Carga Palletizada Textil & Insumos',
                'category' => 'Camión 3/4 & Toco',
                'weight_kg' => 2400,
                'volume_m3' => 14.5,
                'pickup_location' => $origin . ' (Parque Industrial)',
                'dropoff_location' => $targetDropoff,
                'corridor_deviation_km' => 4.2,
                'ready_time' => $returnDate,
                'offered_price' => 1250.00,
                'platform_fee_3_5_pct' => 43.75,
                'net_driver_earnings' => 1206.25,
                'is_pre_bookable' => true,
                'status' => 'open_for_bids',
            ],
            [
                'id' => 202,
                'title' => 'Alimentos Secos y Bebidas en Caja',
                'category' => 'Furgón / VUC',
                'weight_kg' => 1100,
                'volume_m3' => 8.0,
                'pickup_location' => $origin . ' (Centro Logístico)',
                'dropoff_location' => $targetDropoff . ' (Mercado Abasto)',
                'corridor_deviation_km' => 8.5,
                'ready_time' => $returnDate,
                'offered_price' => 680.00,
                'platform_fee_3_5_pct' => 23.80,
                'net_driver_earnings' => 656.20,
                'is_pre_bookable' => true,
                'status' => 'open_for_bids',
            ],
            [
                'id' => 203,
                'title' => 'Maquinaria Ligera & Repuestos',
                'category' => 'Utilitario / Pickup',
                'weight_kg' => 450,
                'volume_m3' => 2.2,
                'pickup_location' => $origin . ' (Carretera Principal)',
                'dropoff_location' => $targetDropoff,
                'corridor_deviation_km' => 1.8,
                'ready_time' => $returnDate,
                'offered_price' => 380.00,
                'platform_fee_3_5_pct' => 13.30,
                'net_driver_earnings' => 366.70,
                'is_pre_bookable' => true,
                'status' => 'open_for_bids',
            ]
        ];

        return response()->json([
            'success' => true,
            'trip_context' => [
                'trip_id' => $trip->id,
                'trip_type' => $trip->trip_type,
                'status' => $trip->status,
                'origin' => $trip->origin_address,
                'outbound_destination' => $trip->destination_address,
                'return_destination' => $targetDropoff,
                'planned_outbound_at' => $trip->planned_outbound_at,
                'planned_return_at' => $trip->planned_return_at,
                'max_corridor_deviation_km' => $trip->max_corridor_deviation_km,
            ],
            'suggested_return_freights' => $corridorFreights,
            'summary' => [
                'total_opportunities' => count($corridorFreights),
                'max_potential_earnings' => array_sum(array_column($corridorFreights, 'net_driver_earnings')),
                'commission_rate' => '3%',
            ]
        ]);
    }

    /**
     * Get all scheduled, active and past trips for a driver.
     */
    public function getDriverTrips(Request $request)
    {
        $driverId = $request->input('driver_id');

        $trips = DB::table('mulacargo_backhauls')
            ->where('driver_id', $driverId)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'trips' => $trips,
        ]);
    }
}
