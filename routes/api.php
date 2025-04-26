<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//auth
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->name('auth.register');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('auth.login');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum')
        ->name('auth.logout');
});

//cruds vendedores
Route::middleware(['auth:sanctum', 'seller', ])->group(function () {
    Route::apiResource('stores', StoreController::class);

    Route::prefix('stores/{store}')->middleware('store.owner')->group(function () {
        Route::apiResource('products', ProductController::class);
    });
});

//crud carrito
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::delete('/cart/items/{product}', [CartController::class, 'removeItem']);
});

