<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SyncLog extends Model
{
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
