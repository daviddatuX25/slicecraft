<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\BookController;

Route::get('/dashboard', function () {
    return view('pages.dashboard');
});

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

Route::get('/p3', function () {
    return view('pages.page3');
});
