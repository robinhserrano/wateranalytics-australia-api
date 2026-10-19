<?php

use App\Models\SyncLog;

// sync_logs grew by ~9k rows/day with nothing deleting them. model:prune now removes rows older than
// SyncLog::KEEP_DAYS, but must keep each command's latest successful run: watermarkFor() reads it.

function syncLogAt(string $command, string $status, int $daysAgo): SyncLog
{
    return SyncLog::create([
        'command' => $command, 'status' => $status,
        'started_at' => now()->subDays($daysAgo), 'completed_at' => now()->subDays($daysAgo)->addSeconds(30),
    ]);
}

function pruneSyncLogs(): void
{
    test()->artisan('model:prune', ['--model' => [SyncLog::class]])->assertExitCode(0);
}

test('rows older than the retention window are pruned, recent ones kept', function () {
    $old = syncLogAt('odoo:sync-sales', 'completed', SyncLog::KEEP_DAYS + 5);
    $oldFailed = syncLogAt('odoo:sync-sales', 'failed', SyncLog::KEEP_DAYS + 3);
    $recent = syncLogAt('odoo:sync-sales', 'completed', 1);

    pruneSyncLogs();

    expect(SyncLog::pluck('id')->all())->toBe([$recent->id]);
});

test('the latest successful run of each command survives even when old, so the watermark is kept', function () {
    syncLogAt('odoo:sync-stocks', 'completed', 40);
    $latestStocks = syncLogAt('odoo:sync-stocks', 'completed', 30);
    $latestInstall = syncLogAt('odoo:sync-installation-dates', 'success', 30); // legacy status
    syncLogAt('odoo:sync-stocks', 'failed', 20); // a later failure must not count as "latest success"
    $watermarkBefore = SyncLog::watermarkFor('odoo:sync-stocks');

    pruneSyncLogs();

    expect(SyncLog::pluck('id')->sort()->values()->all())->toBe([$latestStocks->id, $latestInstall->id])
        ->and(SyncLog::watermarkFor('odoo:sync-stocks')->equalTo($watermarkBefore))->toBeTrue()
        ->and(SyncLog::watermarkFor('odoo:sync-installation-dates', ['completed', 'success']))->not->toBeNull();
});

test('pruning is scheduled daily before the reconcile', function () {
    $this->artisan('schedule:list')->expectsOutputToContain('model:prune');
});
