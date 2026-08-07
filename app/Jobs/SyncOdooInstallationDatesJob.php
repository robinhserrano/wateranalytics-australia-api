<?php

namespace App\Jobs;

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
}
