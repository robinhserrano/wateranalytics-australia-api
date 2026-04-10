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
            RolesAndPermissionsSeeder::class,
            LandingPriceSeeder::class,
        ]);

        $adminUser = User::updateOrCreate(
            ['email' => 'it@wateranalytics.com.au'],
            [
                'name' => 'Digital Support',
                'password' => \Illuminate\Support\Facades\Hash::make('Water@2022!'),
                'email_verified_at' => now(),
            ]
        );

        $adminUser->assignRole('Admin');
    }
}
