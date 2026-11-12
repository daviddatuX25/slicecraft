<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CakeShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BookQueryController;

Route::get('/story', function () {
    return view('pages.story');
});


// NOTE (change NOT in the worksheet instructions): The Laravel example test
// expects the root URL to render successfully, so it remains a simple alias
// for the existing dashboard rather than becoming part of the Page 3 module.
Route::get('/', function (): View {
    return view('pages.dashboard');
});

Route::get('/dashboard', function () {
    return view('pages.dashboard');
})->name('dashboard');

// NOTE (change NOT in the worksheet instructions): The midterm adds a
// database-backed login so the new Page 3 admin modules are protected.
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.authenticate');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/p1', function (Request $request) {
    $q = trim($request->query('search', ''));

    $all = [
        'Inception', 'The Dark Knight', 'Interstellar', 'Titanic', 'Avatar',
        'The Matrix', 'Forrest Gump', 'The Godfather', 'Pulp Fiction', 'Gladiator',
    ];

    // Simple search: keep only movie titles containing the query (case-insensitive).
    $movies = [];
    if ($q === '') {
        // No search word -> show every movie.
        $movies = $all;
    } else {
        // Loop through each movie; if the title contains the search word, keep it.
        foreach ($all as $m) {
            if (stripos($m, $q) !== false) {
                $movies[] = $m;
            }
        }
    }

    return view('pages.page1', ['movies' => $movies, 'q' => $q, 'total' => count($all)]);
});

Route::get('/p2', [BookController::class, 'index']);
Route::get('/book-form', [BookController::class, 'show_add_form']);
Route::post('/add-book', [BookController::class, 'do_add']);
Route::get('/edit-book/{id}', [BookController::class, 'show_edit_form']);
Route::post('/update-book', [BookController::class, 'do_update']);
Route::get('/delete-book/{id}', [BookController::class, 'do_delete']);

Route::middleware('auth')->prefix('p3')->group(function (): void {
    Route::get('/', [CakeShopController::class, 'index'])->name('cake.dashboard');

    Route::resource('products', ProductController::class);

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::patch('/customers/{customer}/status', [CustomerController::class, 'updateStatus'])->name('customers.status.update');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/cart/{cart}', [CartController::class, 'show'])->name('cart.show');
    Route::post('/cart/{cart}/submit', [CartController::class, 'submit'])->name('cart.submit');
    Route::post('/cart/items', [CartController::class, 'storeItem'])->name('cart-items.store');
    Route::patch('/cart/{cart}/items/{orderItem}/quantity', [CartController::class, 'updateQuantity'])->name('cart-items.quantity.update');
    Route::delete('/cart/{cart}/items/{orderItem}', [CartController::class, 'destroyItem'])->name('cart-items.destroy');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

Route::get('/book-query-comparison', [BookQueryController::class, 'index']);


Route::get('/story', function () {
    return view('pages.story');
});

// --------------------------------------------------------------------------
// FINAL EXAM: Online Shopping Website (Customer View)
// --------------------------------------------------------------------------
use App\Http\Controllers\Finals\CustomerAuthViewController;
use App\Http\Controllers\Finals\CustomerStorefrontController;

Route::get('/p4', [CustomerStorefrontController::class, 'index'])->name('finals.page4');

Route::prefix('finals')->group(function (): void {
    // 1. Storefront & Catalog
    Route::get('/', [CustomerStorefrontController::class, 'index'])->name('finals.storefront');
    Route::get('/cart', [CustomerStorefrontController::class, 'cart'])->name('finals.cart');
    Route::post('/cart/items', [CustomerStorefrontController::class, 'addToCart'])->name('finals.cart.items.store');
    Route::patch('/cart/items/{id}', [CustomerStorefrontController::class, 'updateCartItem'])->name('finals.cart.items.update');
    Route::delete('/cart/items/{id}', [CustomerStorefrontController::class, 'removeCartItem'])->name('finals.cart.items.destroy');
    Route::delete('/cart', [CustomerStorefrontController::class, 'clearCart'])->name('finals.cart.clear');

    // Customer Authentication (Dedicated Customer Guard)
    Route::get('/login', [CustomerAuthViewController::class, 'showLogin'])->name('finals.login');
    Route::post('/login', [CustomerAuthViewController::class, 'login'])->name('finals.login.post');
    Route::get('/register', [CustomerAuthViewController::class, 'showRegister'])->name('finals.register');
    Route::post('/register', [CustomerAuthViewController::class, 'register'])->name('finals.register.post');
    Route::post('/logout', [CustomerAuthViewController::class, 'logout'])->name('finals.logout');

    // Protected Customer Routes
    Route::middleware('auth:customer')->group(function (): void {
        Route::get('/checkout', [CustomerStorefrontController::class, 'checkout'])->name('finals.checkout');
        Route::get('/orders', [CustomerStorefrontController::class, 'orders'])->name('finals.orders');
        Route::get('/orders/{id}', [CustomerStorefrontController::class, 'orderDetails'])->name('finals.orders.show');
    });
});

