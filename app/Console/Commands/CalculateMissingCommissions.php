<?php

namespace App\Console\Commands;

use App\Models\SalesOrder;
use App\Services\CommissionCalculator;
use Illuminate\Console\Command;

class CalculateMissingCommissions extends Command
{
    protected $signature = 'commissions:calculate-missing 
                            {--limit=100 : Maximum number of orders to process}
                            {--force : Recalculate even if commission exists}
                            {--all : Recalculate everything without limit}';

    protected $description = 'Calculate commissions for sales orders that don\'t have them yet';

    protected CommissionCalculator $calculator;

    public function __construct(CommissionCalculator $calculator)
    {
        parent::__construct();
        $this->calculator = $calculator;
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $force = $this->option('force');
        $all = $this->option('all');

        if ($all) {
            $limit = 100000; // Effectively no limit for typical usage
            $force = true;
        }

        $query = SalesOrder::query();

        if (!$force) {
            $query->whereDoesntHave('commissionCalculation');
        }

        $ordersToProcess = $query->limit($limit)->get();

        if ($ordersToProcess->isEmpty()) {
            $this->info('No sales orders need commission calculation.');
            return Command::SUCCESS;
        }

        $this->info("Processing {$ordersToProcess->count()} sales orders...");
        
        $bar = $this->output->createProgressBar($ordersToProcess->count());
        $bar->start();

        $successful = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];

        foreach ($ordersToProcess as $order) {
            try {
                $result = $this->calculator->calculateCommission($order);
                if ($result === null) {
                    $skipped++;
                } else {
                    $successful++;
                }
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Order #{$order->id}: {$e->getMessage()}";
                
                // Log the error
                \Log::warning("Commission calculation failed for order {$order->id}", [
                    'error' => $e->getMessage(),
                    'order_id' => $order->id,
                ]);
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info("✓ Successfully calculated: {$successful}");
        
        if ($skipped > 0) {
            $this->warn("⊘ Skipped (no user): {$skipped}");
        }
        
        if ($failed > 0) {
            $this->warn("✗ Failed: {$failed}");
            
            if ($this->option('verbose')) {
                $this->newLine();
                $this->error('Errors:');
                foreach ($errors as $error) {
                    $this->line("  - {$error}");
                }
            }
        }

        return Command::SUCCESS;
    }
}
