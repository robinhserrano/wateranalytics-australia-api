<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Obuchmann\OdooJsonRpc\Odoo;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncOdooInstallationDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:sync-installation-dates {--all : Sync all records, not just missing ones}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Installation Dates from Odoo Stock Moves to Sales Orders';

    /**
     * Cached map of all known local SO names => local ID.
     * Loaded once per command run to avoid repeated full-table scans inside loops.
     *
     * @var array<string, int>|null
     */
    private ?array $knownOrderNames = null;

    // -------------------------------------------------------------------------
    // Odoo field spec
    // -------------------------------------------------------------------------

    private function getSpecification(): array
    {
        return [
            'origin' => (object) [],
            'scheduled_date' => (object) [],
            'product_id' => (object) ['fields' => (object) ['categ_id' => (object) []]],
            'picking_id' => (object) [
                'fields' => (object) [
                    'date_done' => (object) [],
                    'origin' => (object) [],
                    'picking_type_id' => (object) ['fields' => (object) ['code' => (object) []]],
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Safely unpack Odoo records from either a stdClass or array response.
     */
    private function unpackRecords(mixed $response): array
    {
        if (\is_array($response)) {
            return $response['records'] ?? [];
        }
        if (\is_object($response)) {
            return (array) ($response->records ?? []);
        }
        return [];
    }

    /**
     * Resolve a many2one field that Odoo may return as stdClass or legacy tuple.
     */
    private function resolveMany2oneId(mixed $field): ?int
    {
        if (\is_object($field)) {
            return isset($field->id) ? (int) $field->id : null;
        }
        if (\is_array($field)) {
            return isset($field[0]) ? (int) $field[0] : null;
        }
        return null;
    }

    /**
     * Load (and cache) the full SO name => local ID map once per run.
     * Prevents repeated full-table pluck calls inside chunk loops.
     *
     * @return array<string, int>
     */
    private function getKnownOrderNames(): array
    {
        if ($this->knownOrderNames === null) {
            $this->knownOrderNames = SalesOrder::pluck('id', 'name')->toArray();
        }
        return $this->knownOrderNames;
    }

    /**
     * Fetch project.task records for a batch of SO names in one Odoo call.
     * Returns [ so_name => ['task_id' => int, 'deadline' => string|null] ].
     *
     * @param  string[]  $orderNames
     * @return array<string, array{task_id: int, deadline: string|null}>
     */
    private function fetchTaskIdsForOrders(Odoo $odoo, array $orderNames): array
    {
        if (empty($orderNames)) {
            return [];
        }

        try {
            $response = $odoo->executeKw('project.task', 'web_search_read', [
                [['sale_order_id.name', 'in', $orderNames]],
                [
                    'id' => (object) [],
                    'date_deadline' => (object) [],
                    'sale_order_id' => (object) ['fields' => (object) ['name' => (object) []]],
                ],
                0,
                \count($orderNames) * 5,
                'id desc',
            ]);

            $tasks = $this->unpackRecords($response);
            $map = [];

            foreach ($tasks as $task) {
                $task = (object) $task;
                $soName = $task->sale_order_id->name ?? null;
                if ($soName && !isset($map[$soName])) {
                    $map[$soName] = [
                        'task_id' => (int) $task->id,
                        'deadline' => $task->date_deadline ?? null,
                    ];
                }
            }

            return $map;
        } catch (\Exception $e) {
            Log::warning('Could not fetch task IDs for orders: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Bulk-update sales_orders rows using a single CASE...WHEN SQL statement
     * instead of one UPDATE query per row.
     *
     * $updates format:
     *   [ so_name => ['installation_date' => string|null, 'odoo_task_id' => int|null] ]
     *
     * @param  array<string, array{installation_date: string|null, odoo_task_id: int|null}>  $updates
     */
    private function bulkUpdateOrders(array $updates): int
    {
        if (empty($updates)) {
            return 0;
        }

        $knownNames = $this->getKnownOrderNames();

        // Only keep names that actually exist locally
        $filtered = \array_filter($updates, fn($name) => isset($knownNames[$name]), ARRAY_FILTER_USE_KEY);

        if (empty($filtered)) {
            return 0;
        }

        $names = \array_keys($filtered);
        $dateCases = '';
        $taskCases = '';
        $dateBindings = [];
        $taskBindings = [];

        foreach ($filtered as $name => $data) {
            $dateCases .= ' WHEN name = ? THEN ?';
            $dateBindings[] = $name;
            $dateBindings[] = $data['installation_date'];

            $taskCases .= ' WHEN name = ? THEN ?';
            $taskBindings[] = $name;
            $taskBindings[] = $data['odoo_task_id'];
        }

        $placeholders = \implode(',', \array_fill(0, \count($names), '?'));

        // Bindings order must match SQL placeholder order:
        // 1) date CASE bindings, 2) task CASE bindings, 3) WHERE IN bindings
        $bindings = \array_merge($dateBindings, $taskBindings, $names);

        DB::update("
            UPDATE sales_orders
            SET
                installation_date = CASE {$dateCases} ELSE installation_date END,
                odoo_task_id      = CASE {$taskCases} ELSE odoo_task_id END,
                updated_at        = NOW()
            WHERE name IN ({$placeholders})
        ", $bindings);

        return \count($filtered);
    }

    // -------------------------------------------------------------------------
    // Entry point
    // -------------------------------------------------------------------------

    public function handle(Odoo $odoo): int
    {
        $this->info('Starting Odoo Installation Date Sync...');

        $lastSync = null;
        if (!$this->option('all')) {
            $lastSyncRecord = \App\Models\SyncLog::where('command', $this->getName())
                ->where('status', 'success')
                ->latest('completed_at')
                ->first();

            if ($lastSyncRecord) {
                $lastSync = $lastSyncRecord->completed_at;
                $this->info('Performing delta sync since: ' . $lastSync->toDateTimeString());
            }
        }

        if ($this->option('all') || !$lastSync) {
            $this->info('Performing full sync of local orders...');
            $this->performFullSync($odoo);
        } else {
            $this->info('Performing incremental sync from Odoo...');
            $this->performIncrementalSync($odoo, $lastSync);
        }

        $this->info('Installation Date Sync Complete.');

        return 0;
    }

    // -------------------------------------------------------------------------
    // Sync strategies
    // -------------------------------------------------------------------------

    protected function performIncrementalSync(Odoo $odoo, Carbon $lastSync): void
    {
        $domain = [
            ['state', '=', 'done'],
            ['write_date', '>', $lastSync->toDateTimeString()],
            ['product_id.categ_id', 'in', [4, 5, 16]],
        ];

        try {
            $response = $odoo->executeKw('stock.move.line', 'web_search_read', [
                $domain,
                $this->getSpecification(),
                0,
                2000,
                'write_date desc',
            ]);

            $moves = $this->unpackRecords($response);
            $totalFound = \count($moves);

            $this->info("Found {$totalFound} updated moves in Odoo.");

            $updatesCount = $this->processMovesAndSave($odoo, $moves);

            \App\Models\SyncLog::create([
                'command' => $this->getName(),
                'started_at' => now()->subSeconds(now()->diffInSeconds($lastSync)),
                'completed_at' => now(),
                'duration' => now()->diffInSeconds($lastSync),
                'records_processed' => $updatesCount,
                'status' => 'success',
                'message' => "Incremental sync: processed {$totalFound} moves, updated {$updatesCount} local orders.",
            ]);

        } catch (\Exception $e) {
            Log::error('Failed incremental sync: ' . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());

            \App\Models\SyncLog::create([
                'command' => $this->getName(),
                'started_at' => now(),
                'completed_at' => now(),
                'status' => 'error',
                'message' => 'Incremental sync failed: ' . $e->getMessage(),
            ]);
        }
    }

    protected function performFullSync(Odoo $odoo): void
    {
        $query = SalesOrder::query();

        if (!$this->option('all')) {
            $query->whereNull('installation_date');
        }

        $totalToProcess = $query->count();
        $this->info("Found {$totalToProcess} orders to check locally.");

        $bar = $this->output->createProgressBar($totalToProcess);
        $totalUpdates = 0;

        // Select only columns we need — avoids hydrating full Eloquent models
        $query->select(['id', 'name', 'odoo_task_id', 'installation_date'])
            ->chunk(100, function ($orders) use ($odoo, $bar, &$totalUpdates) {
                $orderNames = $orders->pluck('name')->toArray();

                // --- Task sync (one Odoo call, one bulk DB update per chunk) ----
                $taskMap = $this->fetchTaskIdsForOrders($odoo, $orderNames);
                $taskUpdates = [];

                foreach ($orders as $order) {
                    $orderName = $order->name;
                    if (!isset($taskMap[$orderName])) {
                        continue;
                    }

                    $taskId = $taskMap[$orderName]['task_id'];
                    $deadline = $taskMap[$orderName]['deadline'];
                    $currentTaskId = $order->odoo_task_id;
                    $currentInstDate = $order->installation_date;

                    $deadlineChanged = $deadline && (
                        $currentInstDate === null ||
                        Carbon::parse($deadline)->notEqualTo($currentInstDate)
                    );

                    if ((int) $currentTaskId !== (int) $taskId || $deadlineChanged) {
                        $taskUpdates[$orderName] = [
                            'installation_date' => $deadline
                                ? Carbon::parse($deadline)->toDateTimeString()
                                : ($currentInstDate ? Carbon::parse($currentInstDate)->toDateTimeString() : null),
                            'odoo_task_id' => $taskId,
                        ];
                    }
                }

                // Single bulk UPDATE for all task changes in this chunk
                $totalUpdates += $this->bulkUpdateOrders($taskUpdates);

                // --- Stock move sync (one Odoo call per chunk) ------------------
                $domain = [
                    ['state', '=', 'done'],
                    '|',
                    ['origin', 'in', $orderNames],
                    ['picking_id.origin', 'in', $orderNames],
                    ['product_id.categ_id', 'in', [4, 5, 16]],
                ];

                try {
                    $response = $odoo->executeKw('stock.move.line', 'web_search_read', [
                        $domain,
                        $this->getSpecification(),
                        0,
                        1000,
                        'write_date desc',
                    ]);

                    $moves = $this->unpackRecords($response);
                    $totalUpdates += $this->processMovesAndSave($odoo, $moves);

                } catch (\Exception $e) {
                    Log::error('Failed chunk in full sync: ' . $e->getMessage());
                }

                $bar->advance($orders->count());
            });

        $bar->finish();
        $this->newLine();

        \App\Models\SyncLog::create([
            'command' => $this->getName(),
            'started_at' => now(),
            'completed_at' => now(),
            'status' => 'success',
            'records_processed' => $totalUpdates,
            'message' => "Full sync completed, updated {$totalUpdates} orders.",
        ]);
    }

    // -------------------------------------------------------------------------
    // Core move processing
    // -------------------------------------------------------------------------

    protected function processMovesAndSave(Odoo $odoo, array $moves): int
    {
        $knownNames = $this->getKnownOrderNames();

        // Pass 1: resolve best installation date per SO from stock moves
        $updates = [];
        foreach ($moves as $move) {
            $move = (object) $move;

            $pickingTypeCode = $move->picking_id->picking_type_id->code ?? null;
            $isInternal = $pickingTypeCode === 'internal';

            $soName = $isInternal
                ? ($move->picking_id->origin ?? $move->origin ?? null)
                : ($move->origin ?? null);

            if (!$soName || !isset($knownNames[$soName])) {
                continue;
            }

            $installationDate = $isInternal
                ? ($move->scheduled_date ?? null)
                : ($move->picking_id->date_done ?? $move->scheduled_date ?? null);

            if (!$installationDate) {
                continue;
            }

            // Keep latest date among all moves for the same SO
            if (!isset($updates[$soName]) || Carbon::parse($installationDate)->gt(Carbon::parse($updates[$soName]))) {
                $updates[$soName] = $installationDate;
            }
        }

        if (empty($updates)) {
            return 0;
        }

        // Pass 2: batch-fetch task deadlines for all affected SOs in one Odoo call
        $soNames = \array_keys($updates);
        $taskMap = $this->fetchTaskIdsForOrders($odoo, $soNames);

        // Pass 3: merge task deadline + build final update payload
        $finalUpdates = [];
        foreach ($updates as $orderName => $stockMoveDate) {
            $taskId = $taskMap[$orderName]['task_id'] ?? null;
            $taskDeadline = $taskMap[$orderName]['deadline'] ?? null;

            $finalUpdates[$orderName] = [
                // Prefer task deadline; fall back to stock-move date
                'installation_date' => Carbon::parse($taskDeadline ?? $stockMoveDate)->toDateTimeString(),
                'odoo_task_id' => $taskId,
            ];
        }

        // Pass 4: single bulk UPDATE for all SOs
        return $this->bulkUpdateOrders($finalUpdates);
    }
}
