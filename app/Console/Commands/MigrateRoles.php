<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy roles to Spatie permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting role migration...');

        // 1. Get all legacy roles
        $legacyRoles = \DB::table('legacy_roles')->get();

        foreach ($legacyRoles as $legacyRole) {
            $this->info("Creating role: {$legacyRole->name}");
            
            // Create or update Spatie role
            // Guard name defaults to 'web' usually
            \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $legacyRole->name],
                ['guard_name' => 'web']
            );
        }

        // 2. Assign roles to users
        $users = \App\Models\User::all();
        // Since we dropped the FK but not the column, we can still access the raw column via DB or model if casted?
        // Note: The 'role_id' column still exists on users table, just FK dropped.
        // User model still has role_id column in fillable.

        foreach ($users as $user) {
            $roleId = $user->role_id;
            
            if (!$roleId) {
                continue;
            }

            $legacyRole = $legacyRoles->firstWhere('id', $roleId);
            
            if ($legacyRole) {
                $this->info("Assigning role {$legacyRole->name} to user {$user->email}");
                $user->assignRole($legacyRole->name);
            } else {
                $this->warn("User {$user->email} has role_id {$roleId} but no matching legacy role found.");
            }
        }

        $this->info('Role migration completed successfully.');
    }
}
