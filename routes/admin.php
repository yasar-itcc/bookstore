<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\PublisherController;
use Illuminate\Support\Facades\Route;

// Loaded from bootstrap/app.php with the "admin" URL prefix and "admin." route names.

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('books', BookController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('authors', AuthorController::class)->except('show');
    Route::resource('publishers', PublisherController::class)->except('show');
    Route::resource('languages', LanguageController::class)->except('show');
});
