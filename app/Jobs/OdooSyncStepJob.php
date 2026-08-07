<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

/**
 * One step of the chained Odoo sync pipeline (see routes/console.php).
 * Runs the equivalent artisan command via Artisan::call() so the actual
 * sync logic (and its own SyncLogger entry) stays exactly as it is today.
 */
abstract class OdooSyncStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Cache key used as a manual mutex around the whole chain dispatch (see
     * routes/console.php). Bus::chain(...)->dispatch() does not honor
     * ShouldBeUnique on the first job - Laravel only checks it in
     * PendingDispatch (plain Job::dispatch()), never in PendingChain - so
     * this replaces that (previously silently-ignored) protection.
     */
    public const PIPELINE_LOCK_KEY = 'odoo-sync-pipeline-lock';

    public $tries = 3;

    public $timeout = 300;

    public function __construct(public readonly int $syncLogId)
    {
        $this->onQueue('odoo-sync');
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function options(): array;

    abstract protected function command(): string;

    public function handle(): void
    {
        $exitCode = Artisan::call($this->command(), $this->options());

        if ($exitCode !== 0) {
            throw new \RuntimeException(
                "{$this->command()} failed (exit {$exitCode}): ".trim(Artisan::output())
            );
        }
    }

    /**
     * @return array<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addMinutes(20);
    }
}
