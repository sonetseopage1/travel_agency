<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    /**
     * Key holding the full settings map in the cache.
     */
    public const CACHE_KEY = 'site.settings';

    /**
     * The values shipped with a fresh install. Anything an admin has not
     * changed falls back to these, so the site always renders.
     */
    public const DEFAULTS = [
        'site_name' => 'ভ্রমণবিলাস',
        'site_tagline' => 'Travel • Explore • Memories',
        'site_intro' => 'ভ্রমণ হোক সহজ, সুন্দর এবং স্মরণীয়।',
        'footer_note' => 'All rights reserved.',

        'logo' => null,
        'favicon' => null,
        'og_image' => null,

        'meta_title' => null,
        'meta_description' => null,
        'meta_keywords' => null,

        'og_title' => null,
        'og_description' => null,

        'contact_address' => 'ঢাকা, বাংলাদেশ',
        'contact_phone' => '+880 1700 000000',
        'contact_email' => 'hello@banglatraveller.com',

        'social_facebook' => null,
        'social_instagram' => null,
        'social_youtube' => null,
        'social_twitter' => null,
        'social_linkedin' => null,
    ];

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Every setting, merged over the defaults.
     *
     * @return array<string, string|null>
     */
    public static function map(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $stored = static::query()->pluck('value', 'key')->all();

            return array_merge(self::DEFAULTS, $stored);
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::map()[$key] ?? null;

        if ($value === null || $value === '') {
            return $default ?? (self::DEFAULTS[$key] ?? null);
        }

        return $value;
    }

    /**
     * Read a setting with an empty-string fallback, for form fields.
     */
    public static function string(string $key): string
    {
        return (string) static::get($key, '');
    }

    /**
     * Public URL for an image setting, or null when none is set.
     */
    public static function image(string $key): ?string
    {
        $value = static::get($key);

        if (! is_string($value) || $value === '') {
            return null;
        }

        return Tour::resolveImageUrl($value);
    }

    /**
     * The stored path for an image setting, or null.
     */
    public static function imagePath(string $key): ?string
    {
        $value = static::get($key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public static function putMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        static::flush();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
