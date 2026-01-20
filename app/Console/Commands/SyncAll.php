<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SyncLogger;

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
    public function handle(SyncLogger $logger)
    {
        $log = $logger->start('odoo:sync-all');
        $this->info('Starting full Odoo sync...');

        try {
        // 1. Sync Contacts (Partners are needed for Sales)
        $this->info('Step 1/4: Syncing Contacts...');
        if ($this->call('odoo:sync-contacts') !== 0) {
            throw new \Exception('Contact sync failed. Aborting.');
        }

        // 2. Sync Products (Needed for Order Lines)
        $this->info('Step 2/4: Syncing Products...');
        if ($this->call('odoo:sync-products') !== 0) {
            throw new \Exception('Product sync failed. Aborting.');
        }

        // 3. Sync Stocks (Depends on Products)
        $this->info('Step 3/4: Syncing Stocks...');
        if ($this->call('odoo:sync-stocks') !== 0) {
            throw new \Exception('Stock sync failed. Aborting.');
        }

        // 4. Sync Sales Orders (Depends on Contacts and Products)
        $this->info('Step 4/4: Syncing Sales Orders...');
        if ($this->call('odoo:sync-sales') !== 0) {
            throw new \Exception('Sales Order sync failed. Aborting.');
        }

        $this->info('Full sync completed successfully!');
        $logger->complete($log);
        return 0;

        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $logger->fail($log, $e);
            return 1;
        }
    }
}
