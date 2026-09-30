<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Destination;
use App\Models\GalleryPhoto;
use App\Models\Setting;
use App\Models\Tour;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Stores gallery and blog images inside public/images so they ship with the
 * repository and are servable without a storage symlink, matching the
 * convention used for tour and branding images.
 *
 * The database always holds a path relative to the public directory
 * (e.g. "images/gallery-1551234-abcd1234.jpg"), never an absolute URL.
 */
class ContentImageService
{
    public const DIRECTORY = 'images';

    public const MAX_BYTES = 4096;

    public const ALLOWED_MIMES = 'jpg,jpeg,png,webp,webm,gif';

    /**
     * Validation rules for a content image upload.
     *
     * The extension is taken from the detected content type rather than the
     * client-supplied name, so a script renamed to .jpg is rejected and can
     * never land on disk with a script suffix.
     *
     * @return array<int, string>
     */
    public function rules(): array
    {
        return ['required', 'file', 'mimes:'.self::ALLOWED_MIMES, 'max:'.self::MAX_BYTES];
    }

    /**
     * Persist an uploaded image and return its relative path.
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
     * Delete a stored image, unless something still points at it.
     *
     * Photos are routinely reused across the gallery, a tour's gallery array
     * and a blog cover, so a file is only unlinked once no record references
     * it. Remote URLs are always left alone.
     */
    public function delete(?string $path): void
    {
        if (! GalleryPhoto::isLocalImage($path)) {
            return;
        }

        if ($this->isStillReferenced((string) $path)) {
            return;
        }

        $absolute = public_path(str_replace('/', DIRECTORY_SEPARATOR, $path));

        // Guard against any path that escapes the images directory.
        $imagesRoot = realpath(public_path(self::DIRECTORY));

        if ($imagesRoot === false || ! str_starts_with(realpath($absolute) ?: $absolute, $imagesRoot)) {
            return;
        }

        if (is_file($absolute)) {
            File::delete($absolute);
        }
    }

    /**
     * Is any record still using this path?
     *
     * Tables are probed with hasTable guards so the service stays usable
     * while migrations are still being applied.
     */
    public function isStillReferenced(string $path): bool
    {
        if (Schema::hasTable('gallery_photos') && GalleryPhoto::where('image', $path)->exists()) {
            return true;
        }

        if (Schema::hasTable('blog_posts') && BlogPost::where('cover_image', $path)->exists()) {
            return true;
        }

        if (Setting::query()->where('value', $path)->exists()) {
            return true;
        }

        if (Tour::where('cover_image', $path)->exists()) {
            return true;
        }

        if (Destination::where('image', $path)->exists()) {
            return true;
        }

        // A tour gallery is stored as JSON, so it is compared in PHP rather
        // than with a SQL LIKE, which could match one filename in another.
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

    /**
     * Build a collision-proof, human-readable filename.
     */
    private function uniqueName(UploadedFile $file, string $prefix): string
    {
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: $prefix;
        $base = Str::limit($base, 40, '');

        // Derived from the detected content type, never the client-supplied
        // name, so the file cannot land with a script suffix.
        $extension = strtolower((string) ($file->guessExtension() ?: ''));

        if ($extension === '' || ! in_array($extension, explode(',', self::ALLOWED_MIMES), true)) {
            $extension = 'jpg';
        }

        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);

        return $prefix.'-'.$base.'-'.$suffix.'.'.$extension;
    }
}
