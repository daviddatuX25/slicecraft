<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('pages.dashboard');
});

Route::get('/p1', function () {
    return view('pages.page1');
});

Route::get('/p2', function () {
    return view('pages.page2');
});

Route::get('/p3', function () {
    return view('pages.page3');
});
