<?php

namespace App\Models;

use App\Models\Concerns\HasImageUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'title',
    'caption',
    'image',
    'alt_text',
    'destination_id',
    'is_featured',
    'sort_order',
    'is_active',
])]
class GalleryPhoto extends Model
{
    use HasImageUrl;

    public function getImageUrlAttribute(): string
    {
        return self::resolveImageUrl($this->image);
    }

    /**
     * The label shown on the photo card and in the lightbox.
     *
     * Alt text is the accessibility description, so it is deliberately not
     * reused as a visible heading; the caption is the human-facing text.
     */
    public function displayTitle(): string
    {
        return trim((string) ($this->title ?: $this->caption ?: $this->alt_text));
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * The order photos are shown in everywhere: curated first, then the
     * admin-chosen sort order, then newest.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order', 'asc')
            ->orderByDesc('id');
    }

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
