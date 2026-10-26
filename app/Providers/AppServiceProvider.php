<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Emails are sent from the name and address set in Admin → Settings → Email.
        try {
            config(['mail.from' => [
                'address' => Setting::get('email.from_address', config('mail.from.address')),
                'name' => Setting::get('email.from_name', config('mail.from.name')),
            ]]);
        } catch (Throwable) {
            // Settings table not migrated yet.
        }
    }
}
