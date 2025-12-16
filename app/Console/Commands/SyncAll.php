<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:sync-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all Odoo sync commands in the correct order';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting full Odoo sync...');

        // 1. Sync Contacts (Partners are needed for Sales)
        $this->info('Step 1/4: Syncing Contacts...');
        if ($this->call('odoo:sync-contacts') !== 0) {
            $this->error('Contact sync failed. Aborting.');
            return 1;
        }

        // 2. Sync Products (Needed for Order Lines)
        $this->info('Step 2/4: Syncing Products...');
        if ($this->call('odoo:sync-products') !== 0) {
            $this->error('Product sync failed. Aborting.');
            return 1;
        }

        // 3. Sync Stocks (Depends on Products)
        $this->info('Step 3/4: Syncing Stocks...');
        if ($this->call('odoo:sync-stocks') !== 0) {
            $this->error('Stock sync failed. Aborting.');
            return 1;
        }

        // 4. Sync Sales Orders (Depends on Contacts and Products)
        $this->info('Step 4/4: Syncing Sales Orders...');
        if ($this->call('odoo:sync-sales') !== 0) {
            $this->error('Sales Order sync failed. Aborting.');
            return 1;
        }

        $this->info('Full sync completed successfully!');
        return 0;
    }
}
