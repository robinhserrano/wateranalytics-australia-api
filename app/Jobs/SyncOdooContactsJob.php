<?php

namespace App\Jobs;

/**
 * First step of the chained pipeline.
 *
 * NOTE: this used to `implements ShouldBeUnique`, but Laravel's unique-job
 * lock is only honored by PendingDispatch (plain Job::dispatch()) - it is
 * NOT checked anywhere in PendingChain (Bus::chain(...)->dispatch()), so it
 * silently did nothing here. The actual overlap guard now lives in
 * routes/console.php as a manual Cache::add()/forget() mutex around the
 * whole chain dispatch.
 */
class SyncOdooContactsJob extends OdooSyncStepJob
{
    protected function command(): string
    {
        return 'odoo:sync-contacts';
    }

    protected function options(): array
    {
        return [];
    }
}
