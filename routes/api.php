<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/sales-orders', [\App\Http\Controllers\Api\SalesOrderController::class, 'index']);
Route::get('/sales-orders/{id}', [\App\Http\Controllers\Api\SalesOrderController::class, 'show']);
