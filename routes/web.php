<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Admin\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Página Home
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Catálogo de productos
Route::prefix('catalog')->name('catalog.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Catalog/Index');
    })->name('index');

    Route::get('/{slug}', [CatalogController::class, 'show'])->name('show');
});

// Carrito de compras
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Cart/Index');
    })->name('index');
});

// Checkout
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Checkout/Index');
    })->name('index');
});

// Autenticación
Route::prefix('auth')->name('')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Recuperación de contraseña (placeholder)
Route::prefix('password')->name('password.')->group(function () {
    Route::get('/reset', function () {
        return Inertia::render('Auth/ForgotPassword');
    })->name('request');
});

// Perfil de usuario
Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Profile/Dashboard');
    })->name('dashboard');

    Route::get('/orders', function () {
        return Inertia::render('Profile/Orders');
    })->name('orders');
});

// Área de Administración (Protegida)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('dashboard');

    // Rutas de productos (CRUD Completo)
    Route::resource('products', ProductController::class);
});
