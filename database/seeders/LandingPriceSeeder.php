<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\LandingPrice;
use Illuminate\Database\Seeder;

class LandingPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Old landing prices (before April 2025)
        $oldPrices = [
            ['name' => 'WAA Full House Healthy Water V2-B (w filters)', 'internal_reference' => 'FHWR-3S1-20-B', 'product_category' => 'System', 'installation_service' => 4190, 'supply_only' => 3390],
            ['name' => 'WAA Full House Healthy Water V2-W (w filters)', 'internal_reference' => 'FHWR-3S1-20-W', 'product_category' => 'System', 'installation_service' => 4190, 'supply_only' => 3390],
            ['name' => 'WAA Healthy Independent Undersink 6 Stages V1 (w filters)', 'internal_reference' => 'USRO-6S1-2W', 'product_category' => 'System', 'installation_service' => 1490, 'supply_only' => 1190],
            ['name' => 'WAA Healthy Under Sink RO2 (w filters)', 'internal_reference' => 'USRO-3S1-2W', 'product_category' => 'System', 'installation_service' => 300, 'supply_only' => 300],
            ['name' => '3 Way Mixer 7604B (L Circular)', 'internal_reference' => '3WM 7604B', 'product_category' => 'System', 'installation_service' => 300, 'supply_only' => 300],
            ['name' => '3 Way Mixer 7604G (L Circular)', 'internal_reference' => '3WM 7604G', 'product_category' => 'Taps', 'installation_service' => 300, 'supply_only' => 300],
            ['name' => '3 Way Mixer 7604N (L Circular)', 'internal_reference' => '3WM 7604N', 'product_category' => 'Taps', 'installation_service' => 300, 'supply_only' => 300],
            ['name' => '3 Way Mixer 7604S (L Circular)', 'internal_reference' => '3WM 7604S', 'product_category' => 'Taps', 'installation_service' => 300, 'supply_only' => 300],
            ['name' => '3 Way Mixer 7605B (U)', 'internal_reference' => '3WM 7605B', 'product_category' => 'Taps', 'installation_service' => 300, 'supply_only' => 300],
            ['name' => '3 Way Mixer 7605N (U)', 'internal_reference' => '3WM 7605N', 'product_category' => 'Taps', 'installation_service' => 300, 'supply_only' => 300],
            ['name' => '3 Way Mixer 7605S (U)', 'internal_reference' => '3WM 7605S', 'product_category' => 'Taps', 'installation_service' => 300, 'supply_only' => 300],
            ['name' => '3 Way Mixer 7606B (L Flat)', 'internal_reference' => '3WM 7606B', 'product_category' => 'Taps', 'installation_service' => 300, 'supply_only' => 300],
            ['name' => '3 Way Mixer 7606N (L Flat)', 'internal_reference' => '3WM 7606N', 'product_category' => 'Taps', 'installation_service' => 300, 'supply_only' => 300],
            ['name' => '3 Way Mixer 7606S (L Flat)', 'internal_reference' => '3WM 7606S', 'product_category' => 'Taps', 'installation_service' => 300, 'supply_only' => 300],
            ['name' => '3 Way Mixer 7624B (U Detachable)', 'internal_reference' => '3WM 7624B', 'product_category' => 'Taps', 'installation_service' => 400, 'supply_only' => 400],
            ['name' => '3 Way Mixer 7624G (U Detachable)', 'internal_reference' => '3WM 7624G', 'product_category' => 'Taps', 'installation_service' => 400, 'supply_only' => 400],
            ['name' => '3 Way Mixer 7624N (U Detachable)', 'internal_reference' => '3WM 7624N', 'product_category' => 'Taps', 'installation_service' => 400, 'supply_only' => 400],
            ['name' => '3 Way Mixer 7624S (U Detachable)', 'internal_reference' => '3WM 7624S', 'product_category' => 'Taps', 'installation_service' => 400, 'supply_only' => 400],
            ['name' => 'WAA Benchtop 5 in 1 Hydrogen', 'internal_reference' => 'BTRO-5IN1-G1IHCH', 'product_category' => 'Taps', 'installation_service' => 900, 'supply_only' => 900],
        ];

        // New landing prices (from April 2025)
        $newPrices = [
            ['name' => 'WAA Full House Healthy Water V2-B (w filters)', 'internal_reference' => 'FHWR-3S1-20-B', 'product_category' => 'System', 'installation_service' => 4190, 'supply_only' => 3390],
            ['name' => 'WAA Full House Healthy Water V2-W (w filters)', 'internal_reference' => 'FHWR-3S1-20-W', 'product_category' => 'System', 'installation_service' => 4190, 'supply_only' => 3390],
            ['name' => 'WAA Healthy Independent Undersink 6 Stages V1 (w filters)', 'internal_reference' => 'USRO-6S1-2W', 'product_category' => 'System', 'installation_service' => 1490, 'supply_only' => 1190],
            ['name' => 'WAA Healthy Under Sink RO2 (w filters)', 'internal_reference' => 'USRO-3S1-2W', 'product_category' => 'System', 'installation_service' => 700, 'supply_only' => 700],
            ['name' => '3 Way Mixer 7604B (L Circular)', 'internal_reference' => '3WM 7604B', 'product_category' => 'System', 'installation_service' => 0, 'supply_only' => 0],
            ['name' => '3 Way Mixer 7604G (L Circular)', 'internal_reference' => '3WM 7604G', 'product_category' => 'Taps', 'installation_service' => 0, 'supply_only' => 0],
            ['name' => '3 Way Mixer 7604N (L Circular)', 'internal_reference' => '3WM 7604N', 'product_category' => 'Taps', 'installation_service' => 0, 'supply_only' => 0],
            ['name' => '3 Way Mixer 7604S (L Circular)', 'internal_reference' => '3WM 7604S', 'product_category' => 'Taps', 'installation_service' => 0, 'supply_only' => 0],
            ['name' => '3 Way Mixer 7605B (U)', 'internal_reference' => '3WM 7605B', 'product_category' => 'Taps', 'installation_service' => 0, 'supply_only' => 0],
            ['name' => '3 Way Mixer 7605N (U)', 'internal_reference' => '3WM 7605N', 'product_category' => 'Taps', 'installation_service' => 0, 'supply_only' => 0],
            ['name' => '3 Way Mixer 7605S (U)', 'internal_reference' => '3WM 7605S', 'product_category' => 'Taps', 'installation_service' => 0, 'supply_only' => 0],
            ['name' => '3 Way Mixer 7606B (L Flat)', 'internal_reference' => '3WM 7606B', 'product_category' => 'Taps', 'installation_service' => 0, 'supply_only' => 0],
            ['name' => '3 Way Mixer 7606N (L Flat)', 'internal_reference' => '3WM 7606N', 'product_category' => 'Taps', 'installation_service' => 0, 'supply_only' => 0],
            ['name' => '3 Way Mixer 7606S (L Flat)', 'internal_reference' => '3WM 7606S', 'product_category' => 'Taps', 'installation_service' => 0, 'supply_only' => 0],
            ['name' => '3 Way Mixer 7624B (U Detachable)', 'internal_reference' => '3WM 7624B', 'product_category' => 'Taps', 'installation_service' => 0, 'supply_only' => 0],
            ['name' => '3 Way Mixer 7624G (U Detachable)', 'internal_reference' => '3WM 7624G', 'product_category' => 'Taps', 'installation_service' => 0, 'supply_only' => 0],
            ['name' => '3 Way Mixer 7624N (U Detachable)', 'internal_reference' => '3WM 7624N', 'product_category' => 'Taps', 'installation_service' => 0, 'supply_only' => 0],
            ['name' => '3 Way Mixer 7624S (U Detachable)', 'internal_reference' => '3WM 7624S', 'product_category' => 'Taps', 'installation_service' => 0, 'supply_only' => 0],
            ['name' => 'WAA Benchtop 5 in 1 Hydrogen', 'internal_reference' => 'BTRO-5IN1-G1IHCH', 'product_category' => 'Taps', 'installation_service' => 900, 'supply_only' => 900],
        ];

        // Seed old prices (effective before April 2025)
        foreach ($oldPrices as $priceData) {
            $product = Product::where('default_code', $priceData['internal_reference'])->first();
            
            if ($product) {
                LandingPrice::create([
                    'product_id' => $product->id,
                    'name' => $priceData['name'],
                    'internal_reference' => $priceData['internal_reference'],
                    'product_category' => $priceData['product_category'],
                    'installation_service' => $priceData['installation_service'],
                    'supply_only' => $priceData['supply_only'],
                    'effective_from' => '2024-01-01', // Old prices effective from beginning of 2024
                ]);
            }
        }

        // Seed new prices (effective from April 2025)
        foreach ($newPrices as $priceData) {
            $product = Product::where('default_code', $priceData['internal_reference'])->first();
            
            if ($product) {
                LandingPrice::create([
                    'product_id' => $product->id,
                    'name' => $priceData['name'],
                    'internal_reference' => $priceData['internal_reference'],
                    'product_category' => $priceData['product_category'],
                    'installation_service' => $priceData['installation_service'],
                    'supply_only' => $priceData['supply_only'],
                    'effective_from' => '2025-04-01', // New prices effective from April 1, 2025
                ]);
            }
        }

        $this->command->info('Landing prices seeded successfully!');
    }
}
