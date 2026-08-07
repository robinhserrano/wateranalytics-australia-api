<?php

namespace App\Jobs;

use App\Models\SyncLog;
use App\Services\SyncLogger;
use Illuminate\Support\Facades\Cache;

/**
 * Last step of the chained pipeline. Sweeps a bounded batch of orders still
 * missing a commission or still pending - not just the ones synced this run.
 * This is what catches orders that failed salesperson resolution when they
 * were synced, but would resolve correctly now because a local Contact/User
 * mapping was fixed afterward (the sync-triggered recalc in SyncOdooSales-
 * OrdersJob only re-evaluates orders whose Odoo data changed, so it can
 * never pick these up on its own).
 */
class CalculateMissingCommissionsJob extends OdooSyncStepJob
{
    protected function command(): string
    {
        return 'commissions:calculate-missing';
    }

    protected function options(): array
    {
        return [
            '--limit' => 200,
            '--update-unconfirmed' => true,
        ];
    }

    public function handle(): void
    {
        parent::handle();

        app(SyncLogger::class)->complete(
            SyncLog::findOrFail($this->syncLogId),
            0,
            'Pipeline completed.'
        );

        // Release the pipeline mutex acquired in routes/console.php - the
        // failure path releases it via the chain's ->catch() instead.
        Cache::forget(self::PIPELINE_LOCK_KEY);
    }
}
