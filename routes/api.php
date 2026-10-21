<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductStockController;
use App\Http\Controllers\Api\SalesOrderController;
use App\Http\Controllers\Api\TeamController;
use App\Models\CommissionCalculation;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SyncLog;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Read-only sync health for the n8n Odoo-sync monitor (machine caller, so a shared bearer token
// instead of Sanctum). Unset SYNC_HEALTH_TOKEN = endpoint disabled.
// ?ids=1,2,3 returns those Odoo sale.order ids as stored locally, so n8n can diff them against Odoo.
$requireOpsToken = function (Request $request) {
    $token = (string) config('services.sync_health.token');
    abort_unless($token !== '' && hash_equals($token, (string) $request->bearerToken()), 401);
};

Route::get('/sync-health', function (Request $request) use ($requireOpsToken) {
    $requireOpsToken($request);

    $ids = array_slice(array_filter(array_map('intval', explode(',', (string) $request->query('ids')))), 0, 200);

    return [
        'runs' => SyncLog::latest('id')->limit(5)->get(['command', 'status', 'started_at', 'completed_at', 'records_processed', 'message']),
        'counts' => ['sale_orders' => SalesOrder::count()],
        'records' => SalesOrder::whereIn('odoo_id', $ids)->get(['odoo_id', 'state', 'amount_total']),
    ];
});

// Aggregates for the n8n morning/afternoon Discord brief (same token as sync-health). No customer data.
// Orders = confirmed (`sale`) and not deleted in Odoo, like every dashboard. create_date is Odoo's UTC;
// days are cut in the business timezone (Sydney, as the stock pages use).
Route::get('/ops-brief', function (Request $request) use ($requireOpsToken) {
    $requireOpsToken($request);

    $tz = 'Australia/Sydney';
    $today = CarbonImmutable::now($tz)->startOfDay();
    $day = function (CarbonImmutable $start) {
        $row = SalesOrder::where('state', 'sale')->whereNull('odoo_missing_at')
            ->where('create_date', '>=', $start->utc()->toDateTimeString())
            ->where('create_date', '<', $start->addDay()->utc()->toDateTimeString())
            ->selectRaw('count(*) as count, coalesce(sum(amount_total), 0) as revenue')->first();

        return ['date' => $start->toDateString(), 'count' => (int) $row->count, 'revenue' => round((float) $row->revenue, 2)];
    };
    $lastFour = collect(range(1, 4))->map(fn ($weeks) => $day($today->subWeeks($weeks)));

    return [
        'timezone' => $tz,
        'generated_at' => now()->toIso8601String(),
        'orders' => [
            'today' => $day($today), // so far
            'yesterday' => $day($today->subDay()),
            'weekday_avg_4w' => [
                'weekday' => $today->englishDayOfWeek,
                'count' => round($lastFour->avg('count'), 2),
                'revenue' => round($lastFour->avg('revenue'), 2),
            ],
        ],
        'commissions' => [
            'pending' => CommissionCalculation::pending()->count(),
            'auto_rejected_24h' => CommissionCalculation::autoRejected()->where('rejected_at', '>=', now()->subDay())->count(),
        ],
        'last_reconcile' => SyncLog::where('command', 'odoo:reconcile-sales')->latest('id')->first(['status', 'completed_at', 'message']),
        // ponytail: products has no `active` column (archived ones stay locally), so this counts all synced products
        'data_quality' => ['products_without_category' => Product::whereNull('categ_name')->count()],
    ];
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Sales Orders
    Route::get('/sales-orders', [SalesOrderController::class, 'index']);
    Route::get('/sales-orders/{id}', [SalesOrderController::class, 'show']);

    // Commissions
    Route::get('/commissions/stats', [CommissionController::class, 'stats']);
    Route::get('/commissions', [CommissionController::class, 'index']);
    Route::get('/commissions/{id}', [CommissionController::class, 'show']);

    // Teams
    Route::get('/teams', [TeamController::class, 'index']);
    Route::get('/teams/{team}', [TeamController::class, 'show']);

    // Contacts
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::get('/contacts/{id}', [ContactController::class, 'show']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Stocks (Live from Odoo for n8n)
    Route::get('/stocks', [ProductStockController::class, 'index']);
});
