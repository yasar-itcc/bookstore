<?php

namespace App\Support;

/**
 * Colour themes for the storefront, chosen under Settings → Store info. No red in any theme.
 * Each colour overrides the matching --color-* variable from resources/css/theme.css.
 *   navy     headings, dark sections and footer      primary  buttons, links, highlights
 *   cyan     second accent                            leaf     "subscribe", eBook and success badges
 *   page     light section background                 ink / muted / line   text and borders
 */
class Themes
{
    public const DEFAULT = 'ocean';

    public static function all(): array
    {
        return [
            'ocean' => [
                'label' => 'Ocean Blue',
                'description' => 'Navy and bright blue with cyan and green, matching your logo. Fresh and trustworthy.',
                'colors' => [
                    'navy' => '#0B2545', 'navy-light' => '#13315C',
                    'primary' => '#1E6FD9', 'primary-dark' => '#1859B0', 'primary-soft' => '#E8F1FC',
                    'cyan' => '#00B4D8', 'cyan-soft' => '#E0F7FB',
                    'leaf' => '#3BB54A', 'leaf-dark' => '#2E9139', 'leaf-soft' => '#E6F6E8',
                    'page' => '#F5F8FC', 'ink' => '#2F3A4C', 'muted' => '#6B7A90', 'line' => '#E4E9F0',
                ],
            ],
            'emerald' => [
                'label' => 'Emerald Library',
                'description' => 'Deep green with antique gold, like an old library. Calm, classic and premium.',
                'colors' => [
                    'navy' => '#0B3B36', 'navy-light' => '#134E48',
                    'primary' => '#0F8A6F', 'primary-dark' => '#0B6E58', 'primary-soft' => '#E3F4EF',
                    'cyan' => '#C9971C', 'cyan-soft' => '#FBF3DC',
                    'leaf' => '#3BB54A', 'leaf-dark' => '#2E9139', 'leaf-soft' => '#E6F6E8',
                    'page' => '#F4FAF7', 'ink' => '#23332F', 'muted' => '#667A74', 'line' => '#E1ECE8',
                ],
            ],
            'royal' => [
                'label' => 'Royal Plum',
                'description' => 'Rich plum purple with warm gold. Elegant and creative, great for fiction and gifts.',
                'colors' => [
                    'navy' => '#2A1B4A', 'navy-light' => '#3B2766',
                    'primary' => '#6D3FD1', 'primary-dark' => '#5A2FB5', 'primary-soft' => '#F0EAFC',
                    'cyan' => '#D9A021', 'cyan-soft' => '#FCF3DD',
                    'leaf' => '#3BA55C', 'leaf-dark' => '#2F8A4B', 'leaf-soft' => '#E6F5EA',
                    'page' => '#F8F6FC', 'ink' => '#2E2A3B', 'muted' => '#736C85', 'line' => '#E8E3F1',
                ],
            ],
            'saffron' => [
                'label' => 'Saffron & Ink',
                'description' => 'Warm saffron with charcoal ink and teal. Friendly and Indian in feel, like paper and print.',
                'colors' => [
                    'navy' => '#1F2A37', 'navy-light' => '#2B3A4B',
                    'primary' => '#D97706', 'primary-dark' => '#B45309', 'primary-soft' => '#FDF1DE',
                    'cyan' => '#0E9F8E', 'cyan-soft' => '#DDF4F1',
                    'leaf' => '#2E9E5B', 'leaf-dark' => '#23804A', 'leaf-soft' => '#E3F4EA',
                    'page' => '#FFFAF2', 'ink' => '#2D2A26', 'muted' => '#7A7268', 'line' => '#EFE6D8',
                ],
            ],
        ];
    }

    public static function exists(?string $key): bool
    {
        return $key !== null && array_key_exists($key, static::all());
    }

    /**
     * CSS that overrides the brand colour variables, or '' for the default theme.
     */
    public static function css(?string $key): string
    {
        if (! static::exists($key) || $key === self::DEFAULT) {
            return '';
        }

        $vars = collect(static::all()[$key]['colors'])->map(fn ($hex, $name) => "--color-{$name}: {$hex};")->join(' ');

        return ":root { {$vars} }";
    }
}
