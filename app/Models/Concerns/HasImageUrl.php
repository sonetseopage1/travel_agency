<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Resolves stored image references into usable URLs.
 *
 * Stored values are paths relative to the public directory (e.g.
 * "images/photo-123.jpg"), but absolute http(s) URLs are still supported so
 * legacy rows and externally hosted assets keep rendering.
 */
trait HasImageUrl
{
    /**
     * Build a public URL for a stored image reference.
     */
    public static function resolveImageUrl(?string $path): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return asset('images/placeholder.svg');
        }

        if (Str::startsWith($path, ['http://', 'https://', '//', 'data:'])) {
            return $path;
        }

        return asset(Str::startsWith($path, '/') ? ltrim($path, '/') : $path);
    }

    /**
     * @return array<int, string>
     */
    public function getGalleryUrlsAttribute(): array
    {
        return array_values(array_filter(array_map(
            fn ($image) => static::resolveImageUrl($image),
            (array) ($this->gallery ?? [])
        )));
    }

    /**
     * True when the reference points at a locally stored file that can be deleted.
     */
    public static function isLocalImage(?string $path): bool
    {
        $path = trim((string) $path);

        return $path !== ''
            && ! Str::startsWith($path, ['http://', 'https://', '//', 'data:'])
            && Str::startsWith($path, 'images/');
    }
}
