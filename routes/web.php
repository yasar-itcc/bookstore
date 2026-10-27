<?php

use App\Http\Controllers\Store\AuthorController;
use App\Http\Controllers\Store\BlogController;
use App\Http\Controllers\Store\CatalogController;
use App\Http\Controllers\Store\ContactController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\NewsletterController;
use App\Http\Controllers\Store\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('books', [CatalogController::class, 'index'])->name('books.index');
Route::get('books/{book:slug}', [CatalogController::class, 'show'])->name('books.show');

Route::get('authors', [AuthorController::class, 'index'])->name('authors.index');
Route::get('authors/{author:slug}', [AuthorController::class, 'show'])->name('authors.show');

Route::get('about', [PageController::class, 'about'])->name('about');
Route::get('pricing', [PageController::class, 'pricing'])->name('pricing');

Route::get('blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('contact', [ContactController::class, 'create'])->name('contact');
Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

Route::post('newsletter', [NewsletterController::class, 'store'])->middleware('throttle:10,1')->name('newsletter.store');

// Pages written in Admin → Content → Pages (privacy policy, FAQ, …). Kept last; "admin" is reserved for the admin panel.
Route::get('{slug}', [PageController::class, 'show'])->where('slug', '(?!admin$|up$)[a-z0-9-]+')->name('pages.show');
