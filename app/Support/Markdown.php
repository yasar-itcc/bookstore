<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Turns Markdown written in the admin (pages, blog posts) into safe HTML.
 */
class Markdown
{
    public static function toHtml(?string $markdown): string
    {
        $html = Str::markdown((string) $markdown, ['html_input' => 'strip', 'allow_unsafe_links' => false]);

        // Site links like /books/wings-of-fire also work when the store runs in a subfolder (e.g. staging /bookstore).
        $base = rtrim(url('/'), '/');

        return preg_replace('/href="\/(?!\/)/', 'href="'.$base.'/', $html);
    }
}
