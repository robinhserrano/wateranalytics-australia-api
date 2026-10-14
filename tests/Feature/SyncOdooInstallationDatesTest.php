<?php

use App\Models\SalesOrder;
use App\Models\SyncLog;
use Obuchmann\OdooJsonRpc\Odoo;

// odoo:sync-installation-dates used to (1) swallow Odoo errors - exit 0, run logged as fine, and a failed
// task lookup nulled odoo_task_id - and (2) fetch at most 2000 changed moves per run while the watermark
// still advanced past the rest.

function installSyncPreviousRun(): void
{
    // legacy 'success' status, as written before the command moved to SyncLogger
    SyncLog::create(['command' => 'odoo:sync-installation-dates', 'status' => 'success',
        'started_at' => now()->subMinutes(5), 'completed_at' => now()->subMinutes(4)]);
}

function installSyncMove(string $origin, string $date): array
{
    return ['origin' => $origin, 'scheduled_date' => $date, 'picking_id' => (object) [
        'date_done' => $date, 'origin' => $origin, 'picking_type_id' => (object) ['code' => 'outgoing'],
    ]];
}

test('incremental sync pages past the first 2000 changed moves', function () {
    installSyncPreviousRun();
    SalesOrder::create(['odoo_id' => 1, 'name' => 'S001']);

    $offsets = [];
    $this->mock(Odoo::class, function ($mock) use (&$offsets) {
        $mock->shouldReceive('executeKw')->andReturnUsing(function ($model, $method, $args) use (&$offsets) {
            if ($model === 'project.task') {
                return ['records' => []];
            }
            $offsets[] = $args[2];

            return ['records' => $args[2] === 0
                ? array_fill(0, 2000, installSyncMove('S999', '2026-01-01 00:00:00'))
                : [installSyncMove('S001', '2026-10-01 09:00:00')]];
        });
    });

    $this->artisan('odoo:sync-installation-dates')->assertExitCode(0);

    expect($offsets)->toBe([0, 2000])
        ->and((string) SalesOrder::where('name', 'S001')->first()->installation_date)->toStartWith('2026-10-01')
        ->and(SyncLog::latest('id')->first()->status)->toBe('completed');
});

test('an Odoo failure fails the command and its sync log instead of passing silently', function () {
    installSyncPreviousRun();
    $this->mock(Odoo::class, fn ($mock) => $mock->shouldReceive('executeKw')->andThrow(new RuntimeException('Odoo down')));

    $this->artisan('odoo:sync-installation-dates')->assertExitCode(1);

    expect(SyncLog::latest('id')->first())
        ->status->toBe('failed')
        ->message->toBe('Odoo down');
});

test('a failed task lookup fails the run instead of nulling odoo_task_id', function () {
    installSyncPreviousRun();
    SalesOrder::create(['odoo_id' => 1, 'name' => 'S001', 'odoo_task_id' => 77]);

    $this->mock(Odoo::class, function ($mock) {
        $mock->shouldReceive('executeKw')->andReturnUsing(function ($model) {
            if ($model === 'project.task') {
                throw new RuntimeException('task lookup timed out');
            }

            return ['records' => [installSyncMove('S001', '2026-10-01 09:00:00')]];
        });
    });

    $this->artisan('odoo:sync-installation-dates')->assertExitCode(1);

    expect(SalesOrder::where('name', 'S001')->value('odoo_task_id'))->toBe(77);
});
