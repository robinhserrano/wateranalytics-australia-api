<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ExcludeWarehousesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mark MPG and Proto warehouses as excluded
        Warehouse::whereIn('code', ['MMPG', 'MPROT'])
            ->update(['is_excluded' => true]);

        // Make sure all other warehouses are NOT excluded
        Warehouse::whereNotIn('code', ['MMPG', 'MPROT'])
            ->update(['is_excluded' => false]);
    }
}
