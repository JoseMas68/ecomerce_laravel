<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Users\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Users Domain
|--------------------------------------------------------------------------
|
| Routes for user profile management, addresses, and account settings.
| All routes require authentication (auth:sanctum).
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | User Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [UserController::class, 'profile'])->name('users.profile');
    Route::match(['put', 'patch'], '/profile', [UserController::class, 'updateProfile'])->name('users.profile.update');
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('users.change-password');
    Route::post('/upload-avatar', [UserController::class, 'uploadAvatar'])->name('users.upload-avatar');
    Route::delete('/account', [UserController::class, 'deleteAccount'])->name('users.account.delete');

    /*
    |--------------------------------------------------------------------------
    | User Statistics Route
    |--------------------------------------------------------------------------
    */
    Route::get('/stats', [UserController::class, 'stats'])->name('users.stats');

    /*
    |--------------------------------------------------------------------------
    | Address Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('addresses')->group(function () {
        Route::get('/', [UserController::class, 'addresses'])->name('users.addresses.index');
        Route::post('/', [UserController::class, 'storeAddress'])->name('users.addresses.store');
        Route::get('/{id}', [UserController::class, 'showAddress'])->name('users.addresses.show')->where('id', '[0-9]+');
        Route::match(['put', 'patch'], '/{id}', [UserController::class, 'updateAddress'])->name('users.addresses.update')->where('id', '[0-9]+');
        Route::delete('/{id}', [UserController::class, 'deleteAddress'])->name('users.addresses.delete')->where('id', '[0-9]+');
        Route::post('/{id}/set-default', [UserController::class, 'setDefaultAddress'])->name('users.addresses.set-default')->where('id', '[0-9]+');
    });
});
