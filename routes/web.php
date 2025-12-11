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
});

Route::get('/odoo-test', [App\Http\Controllers\OdooController::class, 'index']);
Route::get('/odoo-sales', [App\Http\Controllers\OdooController::class, 'salesOrders']);

require __DIR__.'/debug.php';
