<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MulaCargoFreightSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Utilitario / Pickup',
                'slug' => 'utilitario-pickup',
                'max_weight_kg' => 800.00,
                'max_volume_m3' => 4.00,
                'description' => 'Ideal para envíos urbanos rápidos, piezas industriales ligeras y mudanzas pequeñas. Modelos: Hilux, Strada, Saveiro, Fiorino.',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Furgón / VUC',
                'slug' => 'furgon-vuc',
                'max_weight_kg' => 1800.00,
                'max_volume_m3' => 14.00,
                'description' => 'Protección total contra intemperie para e-commerce, cajas y reparto comercial urbano. Modelos: Sprinter, Master, Transit, HR.',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Camión 3/4 & Toco',
                'slug' => 'camion-3-4-toco',
                'max_weight_kg' => 6000.00,
                'max_volume_m3' => 35.00,
                'description' => 'Transporte de pallets, mercadería pesada interdepartamental y mudanzas grandes. Modelos: Accelo, Delivery, Cargo.',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Truck & Carreta',
                'slug' => 'truck-carreta',
                'max_weight_kg' => 30000.00,
                'max_volume_m3' => 75.00,
                'description' => 'Gran tonelaje, tractocamiones y semirremolques para agroindustria, construcción y carga masiva.',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($categories as $cat) {
            DB::table('mulacargo_freight_categories')->updateOrInsert(
                ['slug' => $cat['slug']],
                $cat
            );
        }

        // Set default commission to 3% in settings if settings table exists
        if (Schema::hasTable('settings')) {
            // Configure platform take rate
        }
    }
}
