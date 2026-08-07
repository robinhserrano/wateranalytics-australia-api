<?php

namespace App\Jobs;

class SyncOdooProductsJob extends OdooSyncStepJob
{
    protected function command(): string
    {
        return 'odoo:sync-products';
    }

    protected function options(): array
    {
        return [];
    }
}
