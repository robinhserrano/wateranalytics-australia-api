<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SyncLog extends Model
{
    use MassPrunable;

    // ponytail: the pipeline writes ~9k rows/day and nothing ever deleted them (1.2M rows by Oct 2026).
    // Two weeks is plenty for "when did it start failing / how long do runs take"; raise if needed.
    public const KEEP_DAYS = 14;

    protected $fillable = [
        'command',
        'status',
        'started_at',
        'completed_at',
        'duration',
        'records_processed',
        'message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Rows `php artisan model:prune` deletes (scheduled daily). Never the latest successful run of each
     * command: watermarkFor() reads it, and losing it would turn the next incremental sync into a full one.
     * The keep-list is computed up front because MySQL can't DELETE with a subquery on the same table.
     */
    public function prunable(): Builder
    {
        $latestSuccessIds = static::whereIn('status', ['completed', 'success'])
            ->groupBy('command')->selectRaw('max(id) as id')->pluck('id');

        return static::where('started_at', '<', now()->subDays(self::KEEP_DAYS))
            ->whereNotIn('id', $latestSuccessIds);
    }

    /**
     * Incremental-sync watermark for $command: the START of its last successful run, minus 1 minute
     * for Odoo/Laravel clock skew. Not completed_at - Odoo edits made while that run was in progress
     * are older than completed_at and would never be fetched. Re-fetching the overlap is harmless
     * since every sync upserts by odoo_id.
     */
    public static function watermarkFor(string $command, string|array $successStatus = 'completed'): ?Carbon
    {
        return static::where('command', $command)
            ->whereIn('status', (array) $successStatus)
            ->latest('completed_at')
            ->first()
            ?->started_at
            ->copy()
            ->subMinute();
    }
}
