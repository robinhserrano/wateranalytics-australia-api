<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;

/**
 * First step of the chained pipeline. Unique across the whole pipeline
 * (not just this step) so a new scheduler tick can't dispatch a second
 * run while one is still in progress - mirrors the old
 * ->withoutOverlapping(15) guard on the synchronous scheduled command.
 */
class SyncOdooContactsJob extends OdooSyncStepJob implements ShouldBeUnique
{
    public function uniqueId(): string
    {
        return 'odoo-sync-pipeline';
    }

    public function uniqueFor(): int
    {
        return 900;
    }

    protected function command(): string
    {
        return 'odoo:sync-contacts';
    }

    protected function options(): array
    {
        return [];
    }
}
