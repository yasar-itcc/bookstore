<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Cuts empty transparent borders off uploaded logos, so they fill the space they're shown in.
 */
class ImageTrimmer
{
    /**
     * Pixels more transparent than this (GD alpha: 0 = solid, 127 = invisible) count as empty,
     * so faint glows or anti-aliasing specks don't stop the trim.
     */
    private const EMPTY_ALPHA = 110;

    private const PADDING = 4;

    public static function trimTransparent(string $path, string $disk = 'public'): void
    {
        $file = Storage::disk($disk)->path($path);
        [$width, $height, $type] = @getimagesize($file) ?: [0, 0, null];

        // Only PNG/WebP can be transparent; skip very large images to keep uploads quick.
        if (! in_array($type, [IMAGETYPE_PNG, IMAGETYPE_WEBP], true) || $width * $height > 6_000_000) {
            return;
        }

        $image = $type === IMAGETYPE_PNG ? @imagecreatefrompng($file) : @imagecreatefromwebp($file);
        if (! $image) {
            return;
        }

        [$left, $top, $right, $bottom] = [$width, $height, -1, -1];
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if (((imagecolorat($image, $x, $y) >> 24) & 0x7F) < self::EMPTY_ALPHA) {
                    $left = min($left, $x);
                    $right = max($right, $x);
                    $top = min($top, $y);
                    $bottom = max($bottom, $y);
                }
            }
        }

        if ($right < 0) {
            return; // Fully transparent: leave it alone.
        }

        $box = [
            'x' => max(0, $left - self::PADDING),
            'y' => max(0, $top - self::PADDING),
            'width' => min($width, $right + self::PADDING + 1) - max(0, $left - self::PADDING),
            'height' => min($height, $bottom + self::PADDING + 1) - max(0, $top - self::PADDING),
        ];

        if ($box['width'] >= $width && $box['height'] >= $height) {
            return; // Nothing to trim.
        }

        $trimmed = imagecrop($image, $box);
        if ($trimmed) {
            imagealphablending($trimmed, false);
            imagesavealpha($trimmed, true);
            $type === IMAGETYPE_PNG ? imagepng($trimmed, $file, 9) : imagewebp($trimmed, $file, 90);
        }
    }
}
