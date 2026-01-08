<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('sales-orders', App\Http\Controllers\SalesOrderController::class)->only(['index', 'show']);
    Route::resource('contacts', App\Http\Controllers\ContactController::class)->only(['index', 'show']);
    Route::resource('stocks', App\Http\Controllers\ProductStockController::class)->only(['index', 'show']);
    Route::resource('products', App\Http\Controllers\ProductController::class)->only(['index', 'show']);
    Route::post('products/{product}/landing-price', [App\Http\Controllers\ProductController::class, 'updateLandingPrice'])->name('products.landing-price.update');
    
    // Commission routes
    Route::resource('commissions', App\Http\Controllers\CommissionController::class)->only(['index', 'show']);
    Route::post('commissions/calculate', [App\Http\Controllers\CommissionController::class, 'calculate'])->name('commissions.calculate');
    Route::post('commissions/{commission}/recalculate', [App\Http\Controllers\CommissionController::class, 'recalculate'])->name('commissions.recalculate');
    Route::post('commissions/{commission}/adjust', [App\Http\Controllers\CommissionController::class, 'adjust'])->name('commissions.adjust');
    Route::post('commissions/{commission}/approve', [App\Http\Controllers\CommissionController::class, 'approve'])->name('commissions.approve');
    Route::post('commissions/{commission}/reject', [App\Http\Controllers\CommissionController::class, 'reject'])->name('commissions.reject');
    Route::post('commissions/{commission}/confirm', [App\Http\Controllers\CommissionController::class, 'confirm'])->name('commissions.confirm');
    Route::post('commissions/{commission}/mark-paid', [App\Http\Controllers\CommissionController::class, 'markAsPaid'])->name('commissions.mark-paid');
    Route::post('commissions/{commission}/mark-odoo', [App\Http\Controllers\CommissionController::class, 'markAsEnteredToOdoo'])->name('commissions.mark-odoo');
    Route::post('commissions/{commission}/reset-confirm', [App\Http\Controllers\CommissionController::class, 'resetConfirm'])->name('commissions.reset-confirm');
    Route::post('commissions/{commission}/reset-odoo', [App\Http\Controllers\CommissionController::class, 'resetOdooSync'])->name('commissions.reset-odoo');
    Route::post('commissions/bulk-approve', [App\Http\Controllers\CommissionController::class, 'bulkApprove'])->name('commissions.bulk-approve');
    Route::get('users/{user}/commission-stats', [App\Http\Controllers\CommissionController::class, 'userStats'])->name('users.commission-stats');
    Route::resource('users', App\Http\Controllers\UserController::class)->only(['index', 'show', 'create', 'store', 'edit', 'update']);
    Route::resource('roles', App\Http\Controllers\RoleController::class)->except(['show']);
    Route::resource('permissions', App\Http\Controllers\PermissionController::class)->only(['store', 'destroy']);
    
    // Teams routes
    Route::resource('teams', App\Http\Controllers\TeamController::class);
    Route::post('teams/{team}/members', [App\Http\Controllers\TeamController::class, 'addMember'])->name('teams.members.add');
    Route::delete('teams/{team}/members/{user}', [App\Http\Controllers\TeamController::class, 'removeMember'])->name('teams.members.remove');
});

Route::get('/odoo-test', [App\Http\Controllers\OdooController::class, 'index']);
Route::get('/odoo-sales', [App\Http\Controllers\OdooController::class, 'salesOrders']);

require __DIR__.'/debug.php';
require __DIR__.'/debug_comm.php';
require __DIR__.'/debug_lines.php';
