<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncLegacyUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legacy:sync-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users from legacy API into the local system';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\LegacyEndpointService $endpointService)
    {
        $this->info('Starting legacy user sync...');

        $legacyUsers = $endpointService->fetchUsers();

        if (empty($legacyUsers)) {
            $this->error('No legacy users found or failed to fetch.');
            return 1;
        }

        $this->info('Fetched ' . count($legacyUsers) . ' legacy users. Processing...');

        $syncedCount = 0;
        $createdCount = 0;
        $updatedCount = 0;

        foreach ($legacyUsers as $legacyUser) {
            $email = $legacyUser['email'] ?? null;
            $legacyId = $legacyUser['id'] ?? null;

            if (!$email || !$legacyId) {
                continue;
            }

            // Find local user by email
            $user = \App\Models\User::where('email', $email)->first();

            $userData = [
                'name' => $legacyUser['name'] ?? 'Legacy User ' . $legacyId,
                'email' => $email,
                'legacy_id' => $legacyId,
                'commission_split' => $legacyUser['commission_split'] ?? null,
                'self_gen_base' => $legacyUser['self_gen'] ?? null,
                'company_lead_base' => $legacyUser['company_lead'] ?? null,
                'email_verified_at' => now(),
                'is_active' => true,
            ];

            // Resolve odoo_user_id by matching email against contacts
            $contact = \App\Models\Contact::where('email', $email)
                ->whereNotNull('odoo_user_ids')
                ->where('odoo_user_ids', '!=', '[]')
                ->first();
            
            if ($contact && !empty($contact->odoo_user_ids)) {
                $userData['odoo_user_id'] = $contact->odoo_user_ids[0];
            }

            if ($user) {
                // Update existing user
                $user->update($userData);
                $updatedCount++;
            } else {
                // Create new user
                $password = 'waaSales!' . $legacyId;
                if (!empty($legacyUser['plain_text'])) {
                    $password = $legacyUser['plain_text'];
                } elseif (!empty($legacyUser['password'])) {
                    $password = $legacyUser['password'];
                }

                $userData['password'] = \Illuminate\Support\Facades\Hash::make($password);
                $user = \App\Models\User::create($userData);
                $createdCount++;
            }

            // Ensure the user has the "Sales Person" role
            if (!$user->hasRole('Admin') && !$user->hasRole('Sales Manager') && !$user->hasRole('Sales Team Manager')) {
                $user->assignRole('Sales Person');
            }

            $syncedCount++;
            if ($syncedCount % 10 === 0) {
                $this->line("Processed $syncedCount users...");
            }
        }

        $this->info("Sync completed. Created: $createdCount, Updated Legacy IDs: $updatedCount, Total Synced: $syncedCount");

        return 0;
    }
}
