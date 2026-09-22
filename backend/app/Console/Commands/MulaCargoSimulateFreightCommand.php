<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MulaCargoSimulateFreightCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mulacargo:simulate {--corridor=scz_montero : Corridor route code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate the end-to-end MulaCargo freight lifecycle: Pre-Trip Radar, AI estimation, QR Escrow and OTP payout.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('====================================================');
        $this->info('   MULACARGO — E2E FREIGHT LIFECYCLE SIMULATOR      ');
        $this->info('====================================================');
        $this->newLine();

        // Step 1: Vehicle & Freight Category Verification
        $this->comment('[1/5] Verificando Categorías de Carga MulaCargo...');
        $categories = [
            ['name' => 'Utilitario / Pickup', 'slug' => 'pickup-hilux', 'max_weight_kg' => 700, 'max_volume_m3' => 2.5],
            ['name' => 'Furgón / VUC', 'slug' => 'furgon-sprinter', 'max_weight_kg' => 1800, 'max_volume_m3' => 12.0],
            ['name' => 'Camión 3/4 & Toco', 'slug' => 'camion-toco', 'max_weight_kg' => 5000, 'max_volume_m3' => 30.0],
            ['name' => 'Heavy Hauler / Carreta', 'slug' => 'carreta-heavy', 'max_weight_kg' => 25000, 'max_volume_m3' => 80.0],
        ];

        foreach ($categories as $cat) {
            $this->line("  ✓ {$cat['name']} (Hasta {$cat['max_weight_kg']} kg / {$cat['max_volume_m3']} m³)");
        }
        $this->newLine();

        // Step 2: Shipper Request & AI Vision Estimation
        $this->comment('[2/5] Simulando Solicitud de Carga & Estimador IA Vision...');
        $cargoDescription = 'Carga Palletizada Textil & Insumos Agrícolas (2 pallets, 2,400 kg)';
        $this->line("  Remitente: Agropecuaria del Oriente S.A.");
        $this->line("  Descripción: {$cargoDescription}");
        $this->line("  Ruta: Santa Cruz de la Sierra (4to Anillo) → Montero (Parque Industrial)");
        $this->line("  Categoría Sugerida por IA: Camión 3/4 & Toco (Confianza: 96%)");
        $this->newLine();

        // Step 3: Pre-Trip Radar de Retorno Matching
        $this->comment('[3/5] Algoritmo Radar de Retorno (Pre-Trip Planning)...');
        $driverName = 'Carlos M. Vaca (Transportes Vaca)';
        $this->line("  Transportista: {$driverName}");
        $this->line("  Base Domicilio: Santa Cruz de la Sierra");
        $this->line("  Viaje de Ida Programado: Santa Cruz → Montero (Mañana 07:30)");
        $this->line("  Disponible para Retorno: Montero → Santa Cruz (Mañana 15:30)");
        $this->line("  Match Corredor Retorno: DETECTADO (Desvío de ruta: +4.2 km)");
        $this->newLine();

        // Step 4: Simple QR Escrow Generation & Platform Fee (3.5%)
        $this->comment('[4/5] Generación de Custodia Escrow con QR Simple Bolivia...');
        $grossFreight = 1250.00;
        $returnDiscount = 437.50; // 35% discount for return load
        $discountedFreight = $grossFreight - $returnDiscount; // Bs. 812.50
        $platformFeeRate = 0.035; // 3.5%
        $platformFee = round($discountedFreight * $platformFeeRate, 2); // Bs. 28.44
        $netCarrierEarnings = round($discountedFreight - $platformFee, 2); // Bs. 784.06
        $bankReference = 'MULA-SCZ-8941';
        $deliveryOtp = '7429';

        $this->line("  Flete Estándar Ida: Bs. " . number_format($grossFreight, 2));
        $this->line("  Tarifa Preferencial Retorno (-35%): Bs. " . number_format($discountedFreight, 2));
        $this->line("  Referencia Interbancaria: {$bankReference} (Simple QR)");
        $this->line("  Estado: [FONDOS EN CUSTODIA ESCROW - 100% PROTEGIDO]");
        $this->newLine();

        // Step 5: Destination Arrival & OTP Payout Settlement
        $this->comment('[5/5] Descarga en Destino & Validación con OTP de Entrega...');
        $this->line("  El receptor ingresa código OTP: {$deliveryOtp}");
        $this->line("  Validación Criptográfica: EXITOSA");
        $this->line("  Liquidación Automática:");
        $this->line("    • Total Cobrado al Remitente: Bs. " . number_format($discountedFreight, 2));
        $this->line("    • Comisión MulaCargo (3.5%):  -Bs. " . number_format($platformFee, 2));
        $this->info("    • GANANCIA NETA AL CONDUCTOR:  Bs. " . number_format($netCarrierEarnings, 2));
        $this->newLine();

        $this->info('====================================================');
        $this->info('   ✓ SIMULACIÓN COMPLETADA EXITOSAMENTE (100%)      ');
        $this->info('====================================================');

        return Command::SUCCESS;
    }
}
