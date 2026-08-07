<?php

namespace App\Jobs;

use App\Models\SyncLog;
use App\Services\SyncLogger;
use Illuminate\Support\Facades\Cache;

class SyncOdooInstallationDatesJob extends OdooSyncStepJob
{
    protected function command(): string
    {
        return 'odoo:sync-installation-dates';
    }

    protected function options(): array
    {
        return [];
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
