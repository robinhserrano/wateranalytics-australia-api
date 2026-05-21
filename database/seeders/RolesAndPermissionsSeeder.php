<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view-all-sales-orders',
            'view-team-sales-orders',
            'view-own-sales-orders',
            'manage-teams',
            'manage-team-members',
            'view-all-commissions',
            'view-team-commissions',
            'view-own-commissions',
            'manage-users',
            'manage-roles',
            'view-installer',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo([
            'view-all-sales-orders',
            'view-all-commissions',
            'manage-teams',
            'manage-team-members',
            'manage-users',
            'manage-roles',
            'view-installer',
        ]);

        $salesManager = Role::firstOrCreate(['name' => 'Sales Manager']);
        $salesManager->givePermissionTo([
            'view-team-sales-orders',
            'view-own-sales-orders',
            'view-team-commissions',
            'view-own-commissions',
            'manage-teams',
            'manage-team-members',
            'view-installer',
        ]);

        $salesTeamManager = Role::firstOrCreate(['name' => 'Sales Team Manager']);
        $salesTeamManager->givePermissionTo([
            'view-team-sales-orders',
            'view-own-sales-orders',
            'view-team-commissions',
            'view-own-commissions',
            'manage-team-members',
        ]);

        $salesPerson = Role::firstOrCreate(['name' => 'Sales Person']);
        $salesPerson->givePermissionTo([
            'view-own-sales-orders',
            'view-own-commissions',
        ]);

        $salesInternal = Role::firstOrCreate(['name' => 'Sales - Internal']);
        $salesInternal->givePermissionTo([
            'view-own-sales-orders',
            'view-own-commissions',
        ]);

        $accountOfficer = Role::firstOrCreate(['name' => 'Account Officer']);
        $accountOfficer->givePermissionTo([
            'view-all-sales-orders',
            'view-all-commissions',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}
