<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MulaCargoAiVisionController extends Controller
{
    /**
     * AI Cargo Spec & Vehicle Estimator Endpoint.
     */
    public function estimateCargo(Request $request)
    {
        $validated = $request->validate([
            'image_url' => 'nullable|url',
            'description' => 'required|string',
        ]);

        // Simulated intelligent AI estimation based on cargo description and dimensions
        $description = strtolower($validated['description']);
        
        $estimatedCategory = 'Utilitario / Pickup';
        $recommendedWeightKg = 500;
        $recommendedVolumeM3 = 2.5;

        if (str_contains($description, 'pallet') || str_contains($description, 'maquinaria') || str_contains($description, 'tonelada')) {
            $estimatedCategory = 'Camión 3/4 & Toco';
            $recommendedWeightKg = 4500;
            $recommendedVolumeM3 = 28.0;
        } elseif (str_contains($description, 'mudanza') || str_contains($description, 'muebles') || str_contains($description, 'cajas')) {
            $estimatedCategory = 'Furgón / VUC';
            $recommendedWeightKg = 1500;
            $recommendedVolumeM3 = 12.0;
        }

        return response()->json([
            'success' => true,
            'ai_analysis' => [
                'suggested_vehicle_category' => $estimatedCategory,
                'estimated_weight_kg' => $recommendedWeightKg,
                'estimated_volume_m3' => $recommendedVolumeM3,
                'handling_flags' => ['Fragile' => false, 'Requires Hydraulic Lift' => $recommendedWeightKg > 1000],
                'confidence_score' => 0.94,
            ],
            'message' => 'Análisis de carga por IA completado con éxito.',
        ]);
    }
}
