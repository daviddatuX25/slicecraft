<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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

Route::get('/p2', function () {
    return view('pages.page2');
});

Route::get('/p3', function () {
    return view('pages.page3');
});
