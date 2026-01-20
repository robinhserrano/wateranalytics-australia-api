<?php

namespace App\Services;

use App\Models\SyncLog;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncLogger
{
    /**
     * Start logging a new sync command.
     */
    public function start(string $command): SyncLog
    {
        return SyncLog::create([
            'command' => $command,
            'status' => 'running',
            'started_at' => now(),
            'records_processed' => 0,
        ]);
    }

    /**
     * Mark the sync as completed successfully.
     */
    public function complete(SyncLog $log, int $count = 0, string $message = null): void
    {
        $now = now();
        
        $log->update([
            'status' => 'completed',
            'completed_at' => $now,
            'duration' => $log->started_at->diffInSeconds($now),
            'records_processed' => $count,
            'message' => $message,
        ]);
        
        Log::info("Sync completed: {$log->command} ($count records)");
    }

    /**
     * Mark the sync as failed.
     */
    public function fail(SyncLog $log, \Throwable|string $exception): void
    {
        $now = now();
        $message = is_string($exception) ? $exception : $exception->getMessage();
        
        $log->update([
            'status' => 'failed',
            'completed_at' => $now,
            'duration' => $log->started_at->diffInSeconds($now),
            'message' => $message,
        ]);

        Log::error("Sync failed: {$log->command} - $message");
    }
    
    /**
     * Log progress update (optional, updates count in real-time)
     */
    public function progress(SyncLog $log, int $count): void
    {
        $log->update(['records_processed' => $count]);
    }
}
