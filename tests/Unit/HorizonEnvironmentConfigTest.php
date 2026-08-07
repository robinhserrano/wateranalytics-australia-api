<?php

use Illuminate\Support\Str;
use Laravel\Horizon\ProvisioningPlan;

test('horizon has a matching supervisor plan for the staging environment', function () {
    // Regression test: config/horizon.php's 'environments' key used to only
    // define 'production' and 'local'. ProvisioningPlan::deploy() takes the
    // first configured key that matches the current APP_ENV via Str::is() -
    // if nothing matches, it silently deploys zero supervisors (no error, no
    // log line - the master process just starts with nothing to run). That's
    // exactly what happened on the staging server: APP_ENV=staging matched
    // neither key, so Horizon reported "Active" with zero worker processes.
    $plan = ProvisioningPlan::get('test-master');

    $matched = collect($plan->parsed)->first(
        fn ($_, $name) => Str::is($name, 'staging')
    );

    expect($matched)->not->toBeNull()
        ->and($matched)->toHaveKey('supervisor-1')
        ->and($matched)->toHaveKey('odoo-sync-supervisor');
});

test('horizon still resolves a dedicated plan for local', function () {
    $plan = ProvisioningPlan::get('test-master');

    $matched = collect($plan->parsed)->first(
        fn ($_, $name) => Str::is($name, 'local')
    );

    expect($matched['supervisor-1']->maxProcesses)->toBe(3);
});
