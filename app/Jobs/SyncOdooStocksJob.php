<?php

namespace App\Jobs;

class SyncOdooStocksJob extends OdooSyncStepJob
{
    protected function command(): string
    {
        return 'odoo:sync-stocks';
    }

    protected function options(): array
    {
        return [];
    }
}
