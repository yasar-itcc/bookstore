<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Fills the slug from the model's name/title when it is left empty, keeping it unique.
 */
trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            $source = $model->slug ?: $model->{$model->slugSource()};
            $model->slug = $model->uniqueSlug(Str::slug($source) ?: Str::lower(Str::random(8)));
        });
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    protected function uniqueSlug(string $slug): string
    {
        $candidate = $slug;
        $suffix = 2;

        while (static::where('slug', $candidate)->whereKeyNot($this->getKey())->exists()) {
            $candidate = "{$slug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
