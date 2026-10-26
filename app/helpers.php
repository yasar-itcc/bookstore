<?php

use App\Models\Setting;
use Illuminate\Support\Number;

if (! function_exists('setting')) {
    /**
     * A store setting, e.g. setting('store.name'). Falls back to the defaults in config/store-settings.php.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('money')) {
    /**
     * Price in rupees with Indian digit grouping, e.g. ₹1,24,999 (paise shown only when needed).
     */
    function money(float|int|string|null $amount): string
    {
        $amount = (float) $amount;
        $decimals = fmod($amount, 1.0) === 0.0 ? 0 : 2;

        return Number::currency($amount, in: 'INR', locale: 'en_IN', precision: $decimals);
    }
}
