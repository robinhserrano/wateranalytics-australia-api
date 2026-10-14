<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SalesOrderController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\DashboardController;
use App\Models\SalesOrder;
use App\Models\SyncLog;

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
Route::get('/sync-health', function (Request $request) {
    $token = (string) config('services.sync_health.token');
    abort_unless($token !== '' && hash_equals($token, (string) $request->bearerToken()), 401);

    $ids = array_slice(array_filter(array_map('intval', explode(',', (string) $request->query('ids')))), 0, 200);

    return [
        'runs' => SyncLog::latest('id')->limit(5)->get(['command', 'status', 'started_at', 'completed_at', 'records_processed', 'message']),
        'counts' => ['sale_orders' => SalesOrder::count()],
        'records' => SalesOrder::whereIn('odoo_id', $ids)->get(['odoo_id', 'state', 'amount_total']),
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
    Route::get('/stocks', [App\Http\Controllers\Api\ProductStockController::class, 'index']);
});
