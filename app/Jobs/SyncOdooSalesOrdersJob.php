<?php

namespace App\Jobs;

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
}
