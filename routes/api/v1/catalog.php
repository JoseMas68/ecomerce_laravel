<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Catalog\BrandController;
use App\Http\Controllers\Api\V1\Catalog\CategoryController;
use App\Http\Controllers\Api\V1\Catalog\ProductController;
use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────
// Catalog API Routes v1
// ──────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {
    // Rutas protegidas (requieren autenticación)

    // Products
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Categories
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Brands
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::put('/brands/{id}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{id}', [BrandController::class, 'destroy'])->name('brands.destroy');
});

// Rutas públicas (no requieren autenticación)

// Products - Listado y detalle
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/search/{query}', [ProductController::class, 'search'])->name('products.search');

// Categories - Listado y detalle
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/categories/tree', [CategoryController::class, 'tree'])->name('categories.tree');

// Brands - Listado y detalle
Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/{id}', [BrandController::class, 'show'])->name('brands.show');
