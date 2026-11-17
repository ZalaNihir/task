<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('categories/tree', [CategoryController::class, 'tree'])->name('categories.tree');
    Route::apiResource('categories',CategoryController::class);
    Route::apiResource('products',ProductController::class);
    Route::apiResource('warehouse',WarehouseController::class);
    Route::post('/stock/adjust', [StockController::class, 'adjust']);
    Route::get('/inventory/summary', [StockController::class, 'summary']);
    Route::get('/products/{id}/movements',[StockController::class, 'history']);
    Route::get('/inventory/low-stock', [StockController::class, 'lowStock']);
});
