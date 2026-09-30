<?php

namespace App\Models;

use App\Models\Concerns\HasImageUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'title',
    'slug',
    'excerpt',
    'body',
    'cover_image',
    'author',
    'category',
    'status',
    'published_at',
    'is_featured',
    'view_count',
    'meta_title',
    'meta_description',
    'meta_keywords',
])]
class BlogPost extends Model
{
    use HasImageUrl;

    /**
     * Valid publication states. `draft` is the default so a post is never
     * published by accident.
     *
     * @var list<string>
     */
    public const STATUSES = ['draft', 'published'];

    public function getCoverImageUrlAttribute(): string
    {
        return self::resolveImageUrl($this->cover_image);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->lessThanOrEqualTo(now());
    }

    /**
     * Only posts that are published and whose date has arrived.
     *
     * The date check lets an admin schedule a post by saving it as published
     * with a future `published_at`.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * The order the public blog listing uses: newest publication first, with
     * featured posts pulled to the top.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('is_featured', 'desc')
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    /**
     * Build a slug from the title, appending -2, -3, ... when needed.
     *
     * Blank titles fall back to a generic stem rather than producing an empty
     * slug, because the column is unique and NOT NULL.
     *
     * @param  int|null  $ignoreId  Exclude this post when checking collisions.
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * A short plain-text preview when the admin did not write an excerpt.
     */
    public function summary(int $limit = 160): string
    {
        $text = trim(strip_tags((string) ($this->excerpt ?: $this->body)));

        return Str::limit($text, $limit);
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'view_count' => 'integer',
        ];
    }
}
