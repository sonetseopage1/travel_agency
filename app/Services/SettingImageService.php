<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\Setting;
use App\Models\Tour;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Stores branding uploads (logo, favicon, social share image) in public/images
 * so they ship with the repository and need no storage symlink.
 *
 * The database holds a path relative to the public directory, matching the
 * convention used for tour images.
 */
class SettingImageService
{
    public const DIRECTORY = 'images';

    public const MAX_BYTES = 4096;

    public const ALLOWED_MIMES = 'jpg,jpeg,png,webp,ico,svg';

    /**
     * Validation rules for a branding upload.
     *
     * The `image` rule is deliberately not used: it rejects SVG and ICO, both
     * of which are common for favicons. `mimes` is resolved from the file
     * content rather than the client-supplied name, so a text file renamed to
     * .png is still rejected.
     *
     * @return array<int, string>
     */
    public function rules(): array
    {
        return ['nullable', 'file', 'mimes:'.self::ALLOWED_MIMES, 'max:'.self::MAX_BYTES];
    }

    /**
     * Persist an uploaded branding file and return its relative path.
     */
    public function store(UploadedFile $file, string $prefix): string
    {
        $directory = public_path(self::DIRECTORY);

        File::ensureDirectoryExists($directory);

        $name = $this->uniqueName($file, $prefix);
        $file->move($directory, $name);

        return self::DIRECTORY.'/'.$name;
    }

    /**
     * Delete a stored file, but only when nothing still points at it.
     *
     * Settings and tour/destination records can share a file, so the check
     * spans every table that stores an image path.
     */
    public function delete(?string $path): void
    {
        if (! Tour::isLocalImage($path)) {
            return;
        }

        if ($this->isStillReferenced((string) $path)) {
            return;
        }

        $absolute = public_path(str_replace('/', DIRECTORY_SEPARATOR, $path));
        $root = realpath(public_path(self::DIRECTORY));

        if ($root === false || ! str_starts_with(realpath($absolute) ?: $absolute, $root)) {
            return;
        }

        if (is_file($absolute)) {
            File::delete($absolute);
        }
    }

    private function isStillReferenced(string $path): bool
    {
        if (Setting::query()->where('value', $path)->exists()) {
            return true;
        }

        if (Tour::where('cover_image', $path)->exists()) {
            return true;
        }

        if (Destination::where('image', $path)->exists()) {
            return true;
        }

        // Gallery is stored as JSON, so compare it in PHP rather than with a
        // database query. The model casts the column to an array.
        return Tour::query()
            ->select('gallery')
            ->cursor()
            ->contains(function ($tour) use ($path) {
                foreach ((array) $tour->gallery as $image) {
                    if ($image === $path) {
                        return true;
                    }
                }

                return false;
            });
    }

    private function uniqueName(UploadedFile $file, string $prefix): string
    {
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: $prefix;
        $base = Str::limit($base, 40, '');

        // Take the extension from the detected content type, never from the
        // client-supplied name, so the file cannot land with a script suffix.
        $extension = strtolower((string) ($file->guessExtension() ?: ''));

        if ($extension === '' || ! in_array($extension, explode(',', self::ALLOWED_MIMES), true)) {
            $extension = 'png';
        }

        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);

        return $prefix.'-'.$base.'-'.$suffix.'.'.$extension;
    }
}
