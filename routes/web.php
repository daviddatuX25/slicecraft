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
    Route::delete('/cart/{cart}/items/{orderItem}', [CartController::class, 'destroyItem'])->name('cart-items.destroy');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});
