<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Store-wide settings (store info, email, SEO, home/about page content), edited under Admin → Settings.
 * Read them with the setting() helper; anything not saved yet falls back to config/store-settings.php.
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    private const CACHE_KEY = 'settings.all';

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::allSettings()[$key] ?? config("store-settings.{$key}");

        return $value ?? $default;
    }

    /**
     * Decoded JSON setting (lists such as team members), or [] when empty.
     */
    public static function json(string $key): array
    {
        $value = static::get($key);

        return is_array($value) ? $value : (json_decode((string) $value, true) ?: []);
    }

    /**
     * Public URL of an uploaded image setting, or null.
     */
    public static function imageUrl(string $key): ?string
    {
        $path = static::get($key);

        return $path ? Storage::disk('public')->url($path) : null;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode(array_values($value)) : $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Every saved setting, cached until the next save.
     *
     * @return array<string, string|null>
     */
    private static function allSettings(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::pluck('value', 'key')->all());
    }
}
