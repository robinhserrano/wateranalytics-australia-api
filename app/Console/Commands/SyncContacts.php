<?php

namespace App\Console\Commands;

use App\Models\Contact;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Obuchmann\OdooJsonRpc\Odoo;

class SyncContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:sync-contacts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Contacts from Odoo using web_search_read';

    /**
     * Execute the console command.
     */
    public function handle(Odoo $odoo)
    {
        $this->info('Starting Odoo Contact Sync...');

        $limit = 500;
        $offset = 0;
        $totalSynced = 0;

        $fields = [
            'display_name',
            'contact_address_complete',
            'parent_id',
            'street',
            'street2',
            'zip',
            'city',
        ];

        do {
            $this->info("Fetching records offset $offset...");

            try {
                $contacts = $odoo->model('res.partner')
                    ->fields($fields)
                    ->limit($limit)
                    ->offset($offset)
                    ->orderBy('id', 'desc')
                    ->get();
            } catch (\Exception $e) {
                $this->error("Failed to fetch from Odoo: " . $e->getMessage());
                Log::error("Odoo Contact Sync Error: " . $e->getMessage());
                return 1;
            }

            if (empty($contacts)) {
                break;
            }

            $bar = $this->output->createProgressBar(count($contacts));
            $bar->start();

            foreach ($contacts as $contact) {
                $parentId = null;
                $parentName = null;

                // Handle parent_id which is usually [id, name] in Odoo JSON-RPC
                if (!empty($contact->parent_id) && is_array($contact->parent_id)) {
                    $parentId = $contact->parent_id[0];
                    $parentName = $contact->parent_id[1];
                }

                Contact::updateOrCreate(
                    ['odoo_id' => $contact->id],
                    [
                        'display_name' => $contact->display_name ?? null,
                        'contact_address_complete' => $contact->contact_address_complete ?? null,
                        'parent_id' => $parentId,
                        'parent_name' => $parentName,
                        'street' => $contact->street ?? null,
                        'street2' => $contact->street2 ?? null,
                        'zip' => $contact->zip ?? null,
                        'city' => $contact->city ?? null,
                    ]
                );

                $totalSynced++;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            $offset += $limit;

            // Check if we've fetched all records
            if (count($contacts) < $limit) {
                break;
            }

        } while (true);

        $this->info("Sync complete. Total records: $totalSynced");
        return 0;
    }
}
