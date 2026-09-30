<?php

namespace App\Models;

use App\Models\Concerns\HasImageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    use HasImageUrl;

    /**
     * Valid tour categories. The `category` column is NOT NULL, so this list is
     * also the validation rule source for the admin forms.
     *
     * @var list<string>
     */
    public const CATEGORIES = [
        'beach',
        'adventure',
        'nature',
        'international',
        'family',
        'historical',
    ];

    protected $fillable = [
        'title',
        'slug',
        'short_title',
        'destination_id',
        'destination',
        'category',
        'country',
        'description',
        'departure_date',
        'return_date',
        'days',
        'nights',
        'departure_location',
        'meeting_point',
        'transport_type',
        'price_per_person',
        'max_slots',
        'current_booked',
        'status',
        'is_featured',
        'cover_image',
        'gallery',
        'itinerary',
        'includes',
        'excludes',
        'important_info',
        'faqs',
        'features',
        'rating',
        'review_count',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date' => 'date',
            'price_per_person' => 'decimal:2',
            'max_slots' => 'integer',
            'current_booked' => 'integer',
            'is_featured' => 'boolean',
            'gallery' => 'array',
            'itinerary' => 'array',
            'includes' => 'array',
            'excludes' => 'array',
            'important_info' => 'array',
            'faqs' => 'array',
            'features' => 'array',
            'rating' => 'decimal:1',
            'review_count' => 'integer',
        ];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getAvailableSlotsAttribute(): int
    {
        return max(0, ($this->max_slots ?? 0) - ($this->current_booked ?? 0));
    }

    public function getPercentageBookedAttribute(): float
    {
        if (! $this->max_slots || $this->max_slots <= 0) {
            return 0;
        }

        return round((($this->current_booked ?? 0) / $this->max_slots) * 100, 1);
    }

    public function getImageAttribute(): ?string
    {
        return $this->cover_image;
    }

    public function getImageUrlAttribute(): string
    {
        return self::resolveImageUrl($this->cover_image);
    }

    public function getLocationAttribute(): string
    {
        return $this->destination
            ?? $this->departure_location
            ?? $this->country
            ?? '';
    }

    public function getDurationDaysAttribute(): int
    {
        return (int) ($this->days ?? 0);
    }

    public function getDurationNightsAttribute(): int
    {
        return (int) ($this->nights ?? 0);
    }

    public function getTotalSeatsAttribute(): int
    {
        return (int) ($this->max_slots ?? 0);
    }

    public function getGalleryImagesAttribute(): array
    {
        return $this->gallery ?? [];
    }

    /**
     * Itinerary days in the shape the admin form writes and the views render:
     * each day has a `day_title` and a list of `activities` objects.
     *
     * Legacy rows store activities as plain strings under a `day`/`title` day
     * key, so those are normalised here instead of being rendered raw.
     *
     * @return list<array{day_title: string, activities: list<array<string, string>>}>
     */
    public function getItineraryDaysAttribute(): array
    {
        $days = [];

        foreach ((array) ($this->itinerary ?? []) as $index => $day) {
            if (is_string($day)) {
                $day = ['title' => $day, 'activities' => []];
            }

            if (! is_array($day)) {
                continue;
            }

            $days[] = [
                'day_title' => $day['day_title'] ?? $day['title'] ?? 'Day '.($index + 1),
                'activities' => $this->normalizeActivities($day['activities'] ?? []),
            ];
        }

        return $days;
    }

    /**
     * @return list<array<string, string>>
     */
    private function normalizeActivities(mixed $activities): array
    {
        $normalized = [];

        foreach ((array) $activities as $activity) {
            if (is_string($activity)) {
                $normalized[] = [
                    'time' => '',
                    'icon' => '📍',
                    'title' => $activity,
                    'location' => '',
                    'description' => '',
                ];

                continue;
            }

            if (is_array($activity)) {
                $normalized[] = [
                    'time' => (string) ($activity['time'] ?? ''),
                    'icon' => (string) ($activity['icon'] ?? '📍'),
                    'title' => (string) ($activity['title'] ?? ''),
                    'location' => (string) ($activity['location'] ?? ''),
                    'description' => (string) ($activity['description'] ?? ''),
                ];
            }
        }

        return $normalized;
    }

    /**
     * FAQ entries keyed consistently for the views.
     *
     * @return list<array{question: string, answer: string}>
     */
    public function getFaqItemsAttribute(): array
    {
        $items = [];

        foreach ((array) ($this->faqs ?? []) as $faq) {
            if (! is_array($faq)) {
                continue;
            }

            $items[] = [
                'question' => (string) ($faq['question'] ?? $faq['q'] ?? ''),
                'answer' => (string) ($faq['answer'] ?? $faq['a'] ?? ''),
            ];
        }

        return $items;
    }

    public function getIsInternationalAttribute(): bool
    {
        return ($this->country ?? 'বাংলাদেশ') !== 'বাংলাদেশ';
    }

    public function getTransportIconAttribute(): string
    {
        return match (true) {
            str_contains((string) $this->transport_type, 'ফ্লাই') => '✈️',
            str_contains((string) $this->transport_type, 'জিপ') => '🚙',
            str_contains((string) $this->transport_type, 'ট্রেন') => '🚆',
            str_contains((string) $this->transport_type, 'নৌকা') => '🚢',
            str_contains((string) $this->transport_type, 'লঞ্চ') => '🚢',
            default => '🚌',
        };
    }
}
