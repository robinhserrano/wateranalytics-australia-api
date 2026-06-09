<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Obuchmann\OdooJsonRpc\Odoo;
use App\Models\SalesOrder;
use Carbon\Carbon;
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
     * Fetch spec — always the same for both sync paths.
     * We request both picking types so we can distinguish outgoing vs internal.
     */
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
     * Resolve a many2one field that Odoo may return as:
     *   - stdClass { id, display_name, ... }
     *   - [id, display_name]  (legacy tuple)
     *   - false / null        (unset)
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
     * Given a list of SO names, fetch the Odoo project.task IDs and deadlines
     * linked to those SOs.
     * Returns a map of [ so_name => ['task_id' => int, 'deadline' => string|null] ].
     */
    private function fetchTaskIdsForOrders(Odoo $odoo, array $orderNames): array
    {
        if (\empty($orderNames)) {
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
     * Execute the console command.
     */
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

        $query->chunk(100, function ($orders) use ($odoo, $bar, &$totalUpdates) {
            $orderNames = $orders->pluck('name')->toArray();

            // 1. Pre-fetch Odoo Tasks so we can link task_id even before stock moves are done
            $taskMap = $this->fetchTaskIdsForOrders($odoo, $orderNames);

            foreach ($orders as $order) {
                // Eloquent models are always objects; guard defensively anyway
                $orderName = \is_object($order) ? $order->name : ($order['name'] ?? null);
                if (!$orderName || !isset($taskMap[$orderName])) {
                    continue;
                }

                $taskId = $taskMap[$orderName]['task_id'];
                $deadline = $taskMap[$orderName]['deadline'];
                $currentTaskId = \is_object($order) ? $order->odoo_task_id : ($order['odoo_task_id'] ?? null);
                $currentInstDate = \is_object($order) ? $order->installation_date : ($order['installation_date'] ?? null);

                $deadlineChanged = $deadline && Carbon::parse($deadline)->notEqualTo($currentInstDate);

                if ((int) $currentTaskId !== (int) $taskId || $deadlineChanged) {
                    SalesOrder::where('name', $orderName)->update([
                        'odoo_task_id' => $taskId,
                        'installation_date' => $deadline ? Carbon::parse($deadline) : $currentInstDate,
                    ]);
                    $totalUpdates++;
                }
            }

            // 2. Query stock moves — match on move origin OR picking origin
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

    protected function processMovesAndSave(Odoo $odoo, array $moves): int
    {
        $knownOrderNames = SalesOrder::pluck('name')->flip()->toArray();

        $updates = [];
        foreach ($moves as $move) {
            $move = (object) $move;

            $pickingTypeCode = $move->picking_id->picking_type_id->code ?? null;
            $isInternal = $pickingTypeCode === 'internal';

            // Resolve SO name:
            //   - Internal transfers : picking_id.origin holds the SO name
            //   - Outgoing / other   : move's own origin field holds the SO name
            $soName = $isInternal
                ? ($move->picking_id->origin ?? $move->origin ?? null)
                : ($move->origin ?? null);

            if (!$soName || !isset($knownOrderNames[$soName])) {
                continue;
            }

            // categ_id comes back as a stdClass — use ->id, not [0]
            // (kept for potential future use; variable intentionally unused for now)
            $categoryId = $this->resolveMany2oneId($move->product_id->categ_id ?? null);

            // Date rule:
            //   - Internal  → scheduled_date  (installer's booked visit date)
            //   - Outgoing  → date_done        (actual delivery/completion date)
            $installationDate = $isInternal
                ? ($move->scheduled_date ?? null)
                : ($move->picking_id->date_done ?? $move->scheduled_date ?? null);

            if (!$installationDate) {
                continue;
            }

            if (!isset($updates[$soName])) {
                $updates[$soName] = $installationDate;
            } else {
                // Keep the latest date among all matching moves for the same SO
                if (Carbon::parse($installationDate)->gt(Carbon::parse($updates[$soName]))) {
                    $updates[$soName] = $installationDate;
                }
            }
        }

        // Batch-fetch task IDs and deadlines for all SO names that have updates
        $soNamesWithUpdates = \array_keys($updates);
        $taskMap = $this->fetchTaskIdsForOrders($odoo, $soNamesWithUpdates);

        foreach ($updates as $orderName => $stockMoveDate) {
            $taskId = $taskMap[$orderName]['task_id'] ?? null;
            $taskDeadline = $taskMap[$orderName]['deadline'] ?? null;

            // Prefer the task deadline; fall back to the stock-move date
            $installationDate = $taskDeadline ?? $stockMoveDate;

            SalesOrder::where('name', $orderName)->update([
                'installation_date' => Carbon::parse($installationDate),
                'odoo_task_id' => $taskId,
            ]);
        }

        return \count($updates);
    }
}