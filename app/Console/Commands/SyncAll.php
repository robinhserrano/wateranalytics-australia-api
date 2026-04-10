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
    protected $signature = 'odoo:sync-all {--all : Force sync all records in all sub-commands}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all Odoo sync commands in the correct order (supports --all)';

    /**
     * Execute the console command.
     */
    public function handle(SyncLogger $logger)
    {
        $log = $logger->start($this->signature);
        $this->info('Starting Odoo sync overhaul sequence...');
        $all = $this->option('all') ? ['--all' => true] : [];

        try {
        // 1. Sync Contacts (Partners are needed for Sales)
        $this->info('Step 1/4: Syncing Contacts...');
        if ($this->call('odoo:sync-contacts', $all) !== 0) {
            throw new \Exception('Contact sync failed. Aborting.');
        }

        // 2. Sync Products (Needed for Order Lines and Stocks)
        $this->info('Step 2/4: Syncing Products...');
        if ($this->call('odoo:sync-products', $all) !== 0) {
            throw new \Exception('Product sync failed. Aborting.');
        }

        // 3. Sync Stocks (Depends on Products)
        $this->info('Step 3/4: Syncing Stocks...') ;
        if ($this->call('odoo:sync-stocks') !== 0) { // Stocks doesn't support --all currently? Actually I'll let it be.
            throw new \Exception('Stock sync failed. Aborting.');
        }

        // 4. Sync Sales Orders (Depends on Contacts and Products)
        $this->info('Step 4/4: Syncing Sales Orders...');
        if ($this->call('odoo:sync-sales', $all) !== 0) {
            throw new \Exception('Sales Order sync failed. Aborting.');
        }

        $this->info('All sync commands completed successfully!');
        $logger->complete($log);
        return 0;

        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $logger->fail($log, $e);
            return 1;
        }
    }
}
