<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\BookHighlightController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\NewsletterSubscriberController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PricingPlanController;
use App\Http\Controllers\Admin\PublisherController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Loaded from bootstrap/app.php with the "admin" URL prefix and "admin." route names.
// The "section" middleware limits each group to the roles that may open it (see App\Enums\Role).

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->middleware('section:dashboard')->name('dashboard');

    Route::middleware('section:catalog')->group(function () {
        Route::resource('books', BookController::class)->except('show');
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('authors', AuthorController::class)->except('show');
        Route::resource('publishers', PublisherController::class)->except('show');
        Route::resource('languages', LanguageController::class)->except('show');
    });

    Route::middleware('section:marketing')->group(function () {
        Route::resource('coupons', CouponController::class)->except('show');
        Route::resource('banners', BannerController::class)->except('show');
        Route::get('highlights', [BookHighlightController::class, 'index'])->name('highlights.index');
        Route::patch('highlights/{book}', [BookHighlightController::class, 'update'])->name('highlights.update');
        Route::get('subscribers', [NewsletterSubscriberController::class, 'index'])->name('subscribers.index');
        Route::get('subscribers/export', [NewsletterSubscriberController::class, 'export'])->name('subscribers.export');
        Route::patch('subscribers/{subscriber}', [NewsletterSubscriberController::class, 'update'])->name('subscribers.update');
        Route::delete('subscribers/{subscriber}', [NewsletterSubscriberController::class, 'destroy'])->name('subscribers.destroy');
        Route::resource('pricing-plans', PricingPlanController::class)->except('show')->parameters(['pricing-plans' => 'pricingPlan']);
    });

    Route::middleware('section:content')->group(function () {
        Route::resource('pages', PageController::class)->except('show');
        Route::resource('posts', PostController::class)->except('show');
        Route::resource('testimonials', TestimonialController::class)->except('show');
    });

    Route::middleware('section:messages')->group(function () {
        Route::get('messages', [ContactMessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [ContactMessageController::class, 'show'])->name('messages.show');
        Route::patch('messages/{message}', [ContactMessageController::class, 'update'])->name('messages.update');
        Route::delete('messages/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');
    });

    Route::middleware('section:settings')->group(function () {
        Route::get('settings/{group?}', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings/{group}', [SettingController::class, 'update'])->name('settings.update');
    });

    Route::resource('users', UserController::class)->except('show')->middleware('section:users');
});
