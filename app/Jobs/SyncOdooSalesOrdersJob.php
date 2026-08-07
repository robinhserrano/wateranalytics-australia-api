<?php

namespace App\Jobs;

use App\Models\SyncLog;
use App\Services\SyncLogger;

class SyncOdooSalesOrdersJob extends OdooSyncStepJob
{
    protected function command(): string
    {
        return 'odoo:sync-sales';
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
    }
}
