<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\LandingPriceSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call([
            // Example of a system user seeder (must run first for authentication)
            // You can keep your test user creation here OR move it to a dedicated seeder.
            // UserSeeder::class, 

            // ProductSeeder::class, 
            LandingPriceSeeder::class, 
            // Add any other seeders here (e.g., RolesSeeder::class, PermissionsSeeder::class)
        ]);
    }
}
