<?php

namespace App\Support;

use App\Models\Book;

/**
 * Draws a simple branded cover (SVG) for books that don't have a real cover image yet.
 */
class CoverGenerator
{
    /**
     * Background / accent colour pairs in the brand palette (no red). Picked per category so each shelf has its own colour.
     */
    private const PALETTES = [
        ['#0B2545', '#00B4D8'],
        ['#1E6FD9', '#8EE3F5'],
        ['#0E7490', '#A5F3FC'],
        ['#2E9139', '#C8F0CC'],
        ['#13315C', '#3BB54A'],
        ['#B45309', '#FDE68A'],
        ['#1859B0', '#FCD34D'],
        ['#155E75', '#3BB54A'],
        ['#334155', '#00B4D8'],
    ];

    public function svg(Book $book): string
    {
        [$background, $accent] = self::PALETTES[($book->category_id ?? 0) % count(self::PALETTES)];

        $titleLines = $this->wrap($book->title, 14, 4);
        $fontSize = count($titleLines) > 2 ? 54 : 64;
        $titleY = 330 - (count($titleLines) - 1) * ($fontSize * 0.6);

        $title = '';
        foreach ($titleLines as $i => $line) {
            $title .= sprintf('<text x="60" y="%d" font-size="%d" font-weight="700" fill="#FFFFFF">%s</text>', $titleY + $i * $fontSize * 1.15, $fontSize, $this->e($line));
        }

        $subtitle = '';
        if ($book->subtitle) {
            $subtitleY = $titleY + count($titleLines) * $fontSize * 1.15 + 10;
            foreach ($this->wrap($book->subtitle, 30, 2) as $i => $line) {
                $subtitle .= sprintf('<text x="60" y="%d" font-size="26" fill="#FFFFFF" fill-opacity="0.85">%s</text>', $subtitleY + $i * 34, $this->e($line));
            }
        }

        $category = $this->e(mb_strtoupper($book->category?->name ?? 'Book Store'));
        $authors = $this->e(str($book->authors->pluck('name')->join(', '))->limit(40));

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 800" width="600" height="800" font-family="'Public Sans', 'Noto Sans Devanagari', 'Segoe UI', Arial, sans-serif">
          <rect width="600" height="800" fill="{$background}"/>
          <circle cx="560" cy="80" r="190" fill="{$accent}" fill-opacity="0.18"/>
          <circle cx="40" cy="790" r="150" fill="{$accent}" fill-opacity="0.14"/>
          <rect x="0" y="0" width="22" height="800" fill="#000000" fill-opacity="0.18"/>
          <rect x="60" y="110" width="70" height="6" rx="3" fill="{$accent}"/>
          <text x="60" y="90" font-size="22" font-weight="600" letter-spacing="3" fill="{$accent}">{$category}</text>
          {$title}
          {$subtitle}
          <rect x="60" y="660" width="480" height="1.5" fill="#FFFFFF" fill-opacity="0.35"/>
          <text x="60" y="708" font-size="30" font-weight="600" fill="#FFFFFF">{$authors}</text>
          <text x="60" y="748" font-size="20" fill="#FFFFFF" fill-opacity="0.6">Book Store</text>
        </svg>
        SVG;
    }

    /**
     * Wide cover (1200×750) for a blog post: category, title and the store name on a brand colour.
     */
    public function postSvg(string $title, ?string $category): string
    {
        [$background, $accent] = self::PALETTES[abs(crc32((string) $category)) % count(self::PALETTES)];

        $lines = $this->wrap($title, 26, 3);
        $fontSize = count($lines) > 2 ? 64 : 76;
        $startY = 375 - (count($lines) - 1) * $fontSize * 0.6;

        $text = '';
        foreach ($lines as $i => $line) {
            $text .= sprintf('<text x="90" y="%d" font-size="%d" font-family="Lora, Georgia, serif" fill="#FFFFFF">%s</text>', $startY + $i * $fontSize * 1.18, $fontSize, $this->e($line));
        }
        $label = $this->e(mb_strtoupper($category ?: 'Blog'));
        $store = $this->e((string) setting('store.name'));

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 750" width="1200" height="750" font-family="'Public Sans', 'Segoe UI', Arial, sans-serif">
          <rect width="1200" height="750" fill="{$background}"/>
          <circle cx="1080" cy="120" r="300" fill="{$accent}" fill-opacity="0.16"/>
          <circle cx="160" cy="760" r="220" fill="{$accent}" fill-opacity="0.12"/>
          <g transform="translate(930 470) rotate(-8)" fill="#FFFFFF" fill-opacity="0.12">
            <rect x="0" y="0" width="150" height="200" rx="8"/><rect x="40" y="-30" width="150" height="200" rx="8"/>
          </g>
          <rect x="90" y="150" width="90" height="7" rx="3.5" fill="{$accent}"/>
          <text x="90" y="125" font-size="28" font-weight="600" letter-spacing="4" fill="{$accent}">{$label}</text>
          {$text}
          <text x="90" y="660" font-size="28" font-weight="600" fill="#FFFFFF" fill-opacity="0.75">{$store} · Blog</text>
        </svg>
        SVG;
    }

    /**
     * Split text into lines of about $width characters, at most $maxLines (the last one ends with an ellipsis if cut).
     *
     * @return list<string>
     */
    private function wrap(string $text, int $width, int $maxLines): array
    {
        $lines = [];
        $current = '';

        foreach (preg_split('/\s+/u', trim($text)) as $word) {
            $candidate = $current === '' ? $word : "{$current} {$word}";
            if ($current !== '' && mb_strlen($candidate) > $width) {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }
        $lines[] = $current;

        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $lines[$maxLines - 1] = rtrim(mb_substr($lines[$maxLines - 1], 0, $width - 1)).'…';
        }

        return $lines;
    }

    private function e(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
