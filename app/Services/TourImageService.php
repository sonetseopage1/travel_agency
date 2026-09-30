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
 * Stores tour images inside public/images so they ship with the repository and
 * are servable without a storage symlink.
 *
 * The database always holds a path relative to the public directory
 * (e.g. "images/cover-coxs-bazar-1712-ab12cd.jpg"), never an absolute URL.
 */
class TourImageService
{
    public const DIRECTORY = 'images';

    public const MAX_GALLERY_IMAGES = 12;

    /**
     * Persist a single uploaded image and return its relative path.
     */
    public function store(UploadedFile $file, string $prefix = 'image'): string
    {
        $directory = public_path(self::DIRECTORY);

        File::ensureDirectoryExists($directory);

        $name = $this->uniqueName($file, $prefix);

        $file->move($directory, $name);

        return self::DIRECTORY.'/'.$name;
    }

    /**
     * Persist many uploaded images, skipping any beyond the gallery limit.
     *
     * @param  array<int, UploadedFile>  $files
     * @return array<int, string>
     */
    public function storeMany(array $files, string $prefix = 'gallery', ?int $limit = null): array
    {
        $limit ??= self::MAX_GALLERY_IMAGES;
        $paths = [];

        foreach ($files as $file) {
            if (count($paths) >= $limit) {
                break;
            }

            if ($file instanceof UploadedFile && $file->isValid()) {
                $paths[] = $this->store($file, $prefix);
            }
        }

        return $paths;
    }

    /**
     * Delete a stored image, unless something still points at it.
     *
     * Seeded photos are shared between several tours and destinations, so a
     * file is only unlinked once no record references it. Remote URLs and
     * paths outside public/images are always left alone.
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
     * Is any tour, destination, gallery, blog or setting still using this path?
     *
     * Tables are probed with hasTable guards so the service stays usable
     * while migrations are still being applied.
     *
     * Gallery values are JSON, so they are compared in PHP rather than with a
     * SQL LIKE, which could match one filename inside another.
     */
    private function isStillReferenced(string $path): bool
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

        $inGallery = Tour::query()
            ->select('id', 'gallery')
            ->cursor()
            ->contains(function ($tour) use ($path) {
                foreach ((array) $tour->gallery as $image) {
                    if ($image === $path) {
                        return true;
                    }
                }

                return false;
            });

        return $inGallery;
    }

    /**
     * @param  array<int, string>  $paths
     */
    public function deleteMany(array $paths): void
    {
        foreach ($paths as $path) {
            $this->delete(is_string($path) ? $path : null);
        }
    }

    /**
     * Build a collision-proof, human-readable filename.
     */
    private function uniqueName(UploadedFile $file, string $prefix): string
    {
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';
        $base = Str::limit($base, 40, '');

        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === '') {
            $extension = $file->guessExtension() ?: 'jpg';
        }

        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);

        return $prefix.'-'.$base.'-'.$suffix.'.'.$extension;
    }
}
