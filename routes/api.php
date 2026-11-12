<?php

use App\Http\Controllers\Api\Finals\CartApiController;
use App\Http\Controllers\Api\Finals\CheckoutApiController;
use App\Http\Controllers\Api\Finals\CustomerAuthApiController;
use App\Http\Controllers\Api\Finals\OrderApiController;
use App\Http\Controllers\Api\Finals\ProductApiController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('post', PostController::class);

/*
|--------------------------------------------------------------------------
| Final Exam: Customer View RESTful API Endpoints
|--------------------------------------------------------------------------
| Implements the 5 rubric modules for the online shopping website:
| 1. Customer Account Module (Register, Login, Logout, Profile)
| 2. Product Browsing Module (Catalog, Query Builder/Eloquent Search)
| 3. Shopping Cart Module (Add, View, Update quantity, Remove item)
| 4. Checkout & Order Processing (DB::transaction & Laravel Queue dispatch)
| 5. Customer Order Management (Customer Order History & Privacy Enforcement)
*/
Route::prefix('v1/customer')->group(function (): void {
    // 1. Customer Account Module
    Route::post('/register', [CustomerAuthApiController::class, 'register'])->name('api.customer.register');
    Route::post('/login', [CustomerAuthApiController::class, 'login'])->name('api.customer.login');

    // 2. Product Browsing Module (Public)
    Route::get('/products', [ProductApiController::class, 'index'])->name('api.customer.products.index');
    Route::get('/products/{id}', [ProductApiController::class, 'show'])->name('api.customer.products.show');

    // 3. Shopping Cart Module (Accessible via Sanctum Token, Customer Session, or Guest)
    Route::get('/cart', [CartApiController::class, 'index'])->name('api.customer.cart.index');
    Route::post('/cart/items', [CartApiController::class, 'store'])->name('api.customer.cart.items.store');
    Route::patch('/cart/items/{id}', [CartApiController::class, 'update'])->name('api.customer.cart.items.update');
    Route::delete('/cart/items/{id}', [CartApiController::class, 'destroy'])->name('api.customer.cart.items.destroy');
    Route::delete('/cart', [CartApiController::class, 'clear'])->name('api.customer.cart.clear');

    // Protected Customer Routes
    Route::middleware('auth:sanctum,customer')->group(function (): void {
        Route::get('/me', [CustomerAuthApiController::class, 'me'])->name('api.customer.me');
        Route::post('/logout', [CustomerAuthApiController::class, 'logout'])->name('api.customer.logout');

        // 4. Checkout & Order Processing
        Route::get('/checkout/summary', [CheckoutApiController::class, 'summary'])->name('api.customer.checkout.summary');
        Route::post('/checkout', [CheckoutApiController::class, 'checkout'])->name('api.customer.checkout');

        // 5. Customer Order Management
        Route::get('/orders', [OrderApiController::class, 'index'])->name('api.customer.orders.index');
        Route::get('/orders/{id}', [OrderApiController::class, 'show'])->name('api.customer.orders.show');
    });
});
