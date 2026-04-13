<?php

namespace App\Console\Commands;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\SyncLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Obuchmann\OdooJsonRpc\Odoo;
use App\Services\SyncLogger;

class SyncContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:sync-contacts {--all : Force sync all records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Contacts from Odoo using web_search_read';

    /**
     * Execute the console command.
     */
    public function handle(Odoo $odoo, SyncLogger $logger)
    {
        $log = $logger->start($this->signature);
        $this->info('Starting Odoo Contact Sync...');

        $lastSync = null;
        if (!$this->option('all')) {
            $lastSyncRecord = SyncLog::where('command', $this->signature)
                ->where('status', 'completed')
                ->latest('completed_at')
                ->first();
            
            if ($lastSyncRecord) {
                $lastSync = $lastSyncRecord->completed_at;
                $this->info("Performing delta sync since: " . $lastSync->toDateTimeString());
            }
        }

        $limit = 1000;
        $offset = 0;
        $totalSynced = 0;

        try {
            $domain = [];
            if ($lastSync) {
                $domain[] = ['write_date', '>', $lastSync->toDateTimeString()];
            }
            $fields = [
                'display_name',
                'contact_address_complete',
                'parent_id',
                'street',
                'street2',
                'zip',
                'city',
                'state_id',
                'phone',
                'email',
                'category_id',
                'user_ids',
                'write_date',
            ];

            // Fetch all categories from Odoo once to map them
            $this->info('Fetching categories from Odoo...');
            try {
                $odooCategories = $odoo->model('res.partner.category')
                    ->fields(['name', 'color'])
                    ->get();
                
                foreach ($odooCategories as $cat) {
                    Tag::updateOrCreate(
                        ['odoo_id' => $cat->id],
                        [
                            'name' => $cat->name,
                            'color' => $cat->color,
                        ]
                    );
                }
            } catch (\Exception $e) {
                $this->warn('Could not fetch categories: ' . $e->getMessage());
            }

            do {
                $this->info("Fetching records offset $offset...");

                $specification = [];
                foreach ($fields as $field) {
                    $specification[$field] = (object)[];
                }

                $response = $odoo->executeKw('res.partner', 'web_search_read', [
                    $domain,
                    $specification,
                    $offset,
                    $limit,
                    'id desc'
                ]);

                $contacts = $response->records ?? (is_array($response) ? ($response['records'] ?? []) : []);

                if (empty($contacts)) {
                    break;
                }

                $syncData = [];
                $contactTags = [];
                $odooIds = [];

                foreach ($contacts as $contact) {
                    $contact = (object)$contact;
                    $odooIds[] = $contact->id;
                    $parentId = null;
                    $parentName = null;

                    if (!empty($contact->parent_id) && is_array($contact->parent_id)) {
                        $parentId = $contact->parent_id[0];
                        $parentName = $contact->parent_id[1];
                    }

                    $syncData[] = [
                        'odoo_id' => $contact->id,
                        'display_name' => $contact->display_name ?? null,
                        'contact_address_complete' => $contact->contact_address_complete ?? null,
                        'parent_id' => $parentId,
                        'parent_name' => $parentName,
                        'street' => $contact->street ?? null,
                        'street2' => $contact->street2 ?? null,
                        'zip' => $contact->zip ?? null,
                        'city' => $contact->city ?? null,
                        'state' => is_array($contact->state_id) ? $contact->state_id[1] : null,
                        'phone' => $contact->phone ?? null,
                        'email' => $contact->email ?? null,
                        'odoo_user_ids' => !empty($contact->user_ids) ? json_encode($contact->user_ids) : null,
                        'write_date' => $contact->write_date ?? null,
                        'updated_at' => Carbon::now(),
                    ];

                    if (!empty($contact->category_id) && is_array($contact->category_id)) {
                        $contactTags[$contact->id] = $contact->category_id;
                    } else {
                        $contactTags[$contact->id] = [];
                    }
                }

                $this->info("Upserting " . count($syncData) . " contacts...");
                Contact::upsert($syncData, ['odoo_id'], [
                    'display_name', 'contact_address_complete', 'parent_id', 'parent_name', 
                    'street', 'street2', 'zip', 'city', 'state', 'phone', 'email', 'odoo_user_ids', 'write_date', 'updated_at'
                ]);

                // Bulk sync tags to avoid N+1 sync() calls
                $dbContacts = Contact::whereIn('odoo_id', $odooIds)->get();
                $allOdooTagIds = collect($contactTags)->flatten()->unique();
                $allTags = Tag::whereIn('odoo_id', $allOdooTagIds)->pluck('id', 'odoo_id')->toArray();

                $pivotData = [];
                $contactIdsToClean = $dbContacts->pluck('id')->toArray();

                foreach ($dbContacts as $dbContact) {
                    $odooTagIds = $contactTags[$dbContact->odoo_id] ?? [];
                    foreach ($odooTagIds as $oid) {
                        if (isset($allTags[$oid])) {
                            $pivotData[] = [
                                'contact_id' => $dbContact->id,
                                'tag_id' => $allTags[$oid],
                            ];
                        }
                    }
                }

                // Delete existing pivot entries for these contacts and bulk insert new ones
                \Illuminate\Support\Facades\DB::transaction(function () use ($contactIdsToClean, $pivotData) {
                    \Illuminate\Support\Facades\DB::table('contact_tag')->whereIn('contact_id', $contactIdsToClean)->delete();
                    if (!empty($pivotData)) {
                        \Illuminate\Support\Facades\DB::table('contact_tag')->insert($pivotData);
                    }
                });

                // Refresh denormalized columns for this batch
                foreach ($dbContacts as $dbContact) {
                    $dbContact->refreshDenormalizedData();
                }

                $totalSynced += count($contacts);
                $offset += $limit;

                if (count($contacts) < $limit) {
                    break;
                }

            } while (true);

            $this->info("Sync complete. Total records: $totalSynced");

            // Backfill odoo_user_ids from res.users for contacts that have it empty.
            // This catches ghost contacts (e.g., Contact 5611 whose User 493 has partner_id=5611).
            $this->info('Backfilling odoo_user_ids from Odoo res.users...');
            try {
                // Build partner_id → [user_ids] map from res.users
                $partnerToUserIds = [];
                $userOffset = 0;
                $userLimit = 1000;
                do {
                    $userResponse = $odoo->executeKw('res.users', 'web_search_read', [
                        [['active', 'in', [true, false]]],
                        ['partner_id' => (object)['fields' => (object)['id' => (object)[], 'display_name' => (object)[]]], 'active' => (object)[]],
                        $userOffset,
                        $userLimit,
                        'id asc'
                    ]);
                    $odooUsers = $userResponse->records ?? (is_array($userResponse) ? ($userResponse['records'] ?? []) : []);
                    foreach ($odooUsers as $u) {
                        $u = (object)$u;
                        $partnerRaw = $u->partner_id ?? null;
                        
                        $partnerId = null;
                        $partnerName = 'Unknown (Ghost)';
                        
                        if (is_array($partnerRaw)) {
                            $partnerId = $partnerRaw[0] ?? null;
                            $partnerName = $partnerRaw[1] ?? 'Unknown (Ghost)';
                        } elseif (is_object($partnerRaw)) {
                            $partnerId = $partnerRaw->id ?? null;
                            $partnerName = $partnerRaw->display_name ?? 'Unknown (Ghost)';
                        }

                        $isActive = $u->active ?? true; // Default to true if not provided
                        $suffixedUserId = $isActive ? (string)$u->id : $u->id . '-G';

                        if ($partnerId) {
                            $partnerToUserIds[$partnerId]['user_ids'][] = $suffixedUserId;
                            $partnerToUserIds[$partnerId]['name'] = $partnerName;
                        }
                    }
                    $userOffset += $userLimit;
                } while (count($odooUsers) === $userLimit);

                $this->info('Fetched ' . count($partnerToUserIds) . ' user→partner mappings from res.users.');

                $backfilled = 0;
                $upsertData = [];
                $now = \Carbon\Carbon::now();

                foreach ($partnerToUserIds as $partnerId => $data) {
                    $upsertData[] = [
                        'odoo_id' => $partnerId,
                        // We use the name returned directly by the res.users -> partner_id relation
                        'display_name' => $data['name'],
                        'odoo_user_ids' => json_encode($data['user_ids']),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Batch upsert to guarantee they exist, even if standard res.partner search hid them!
                if (!empty($upsertData)) {
                    \App\Models\Contact::upsert($upsertData, ['odoo_id'], ['display_name', 'odoo_user_ids', 'updated_at']);
                    $backfilled = count($upsertData);
                }

                $this->info("Refreshed/Upserted odoo_user_ids on {$backfilled} contacts (guaranteed visibility).");
            } catch (\Exception $e) {
                $this->warn('Could not backfill odoo_user_ids: ' . $e->getMessage());
            }

            $logger->complete($log, $totalSynced, $lastSync ? "Incremental sync completed." : "Full sync completed.");
            \Illuminate\Support\Facades\Cache::forget('contacts_dashboard_stats');
            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to fetch from Odoo: " . $e->getMessage());
            Log::error("Odoo Contact Sync Error: " . $e->getMessage());
            $logger->fail($log, $e);
            return 1;
        }
    }
}
