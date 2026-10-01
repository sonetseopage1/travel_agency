<?php

namespace App\Models;

use App\Models\Concerns\HasImageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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

    /**
     * Valid publication states. Shared by the admin filter, the status
     * dropdowns on the create/edit forms and the validation rules, so all
     * three always agree.
     *
     * @var list<string>
     */
    public const STATUSES = ['draft', 'published', 'unpublished', 'completed'];

    /**
     * Suggestions offered by the admin transport field.
     *
     * This is guidance only, not a whitelist: the column stores free text so a
     * tour can say "AC Bus" or "ট্রেন + লঞ্চ", and getTransportIconAttribute()
     * matches on substrings.
     *
     * @var list<string>
     */
    public const TRANSPORT_TYPES = [
        'AC Bus',
        'বাস',
        'জিপ',
        'ট্রেন',
        'ফ্লাইট',
        'নৌকা',
        'লঞ্চ',
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

    /**
     * The travel window as a single readable range, in Bangla.
     *
     * Every card and the detail page need the same wording, so the formatting
     * lives here rather than being repeated per view. A tour with no
     * departure date returns null and the caller omits the row entirely,
     * rather than showing a bare "TBD".
     */
    public function getTravelDateLabelAttribute(): ?string
    {
        if (empty($this->departure_date)) {
            return null;
        }

        $start = Carbon::parse($this->departure_date);

        // A return date is only meaningful when it lands on or after the
        // departure; a reversed pair is treated as absent rather than shown
        // as an impossible range.
        $end = empty($this->return_date) ? null : Carbon::parse($this->return_date);
        $end = ($end && ! $end->lessThan($start)) ? $end : null;

        // A single day is one date, not a "10 - 10" range.
        if (! $end) {
            return $start->format('j F Y');
        }

        if ($end->isSameDay($start)) {
            return $start->format('j F Y');
        }

        // Same month and year reads better collapsed: "10 - 13 November 2026".
        if ($start->isSameMonth($end) && $start->isSameYear($end)) {
            return $start->format('j').' - '.$end->format('j F Y');
        }

        if ($start->isSameYear($end)) {
            return $start->format('j F').' - '.$end->format('j F Y');
        }

        return $start->format('j F Y').' - '.$end->format('j F Y');
    }

    /**
     * The same window in a compact form for tight card layouts, where the full
     * range would wrap onto a third line.
     */
    public function getTravelDateShortAttribute(): ?string
    {
        if (empty($this->departure_date)) {
            return null;
        }

        $start = Carbon::parse($this->departure_date);
        $end = empty($this->return_date) ? null : Carbon::parse($this->return_date);
        $end = ($end && ! $end->lessThan($start)) ? $end : null;

        if (! $end) {
            return $start->format('j M y');
        }

        if ($end->isSameDay($start)) {
            return $start->format('j M y');
        }

        if ($start->isSameMonth($end) && $start->isSameYear($end)) {
            return $start->format('j').'-'.$end->format('j M y');
        }

        return $start->format('j M').' - '.$end->format('j M y');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function pricingTiers(): HasMany
    {
        return $this->hasMany(TourPricingTier::class);
    }

    /**
     * Tiers a customer may actually book: active only, ordered cheapest-first so
     * the default selection is the entry-level option.
     *
     * Reuses an eager-loaded relation when the caller has already loaded it,
     * which avoids a second query on the listing pages.
     *
     * @return Collection<int, TourPricingTier>
     */
    public function bookableTiers()
    {
        $relation = $this->relationLoaded('pricingTiers') ? $this->pricingTiers : null;

        $tiers = $relation ?? $this->pricingTiers()->active()->ordered()->get();

        return collect($tiers)
            ->filter(fn (TourPricingTier $tier) => $tier->is_active)
            ->sortBy('price_per_adult')
            ->values();
    }

    public function getStartingPriceAttribute(): float
    {
        return (float) ($this->bookableTiers()->first()?->price_per_adult ?? $this->price_per_person ?? 0);
    }

    public function getHasPricingTiersAttribute(): bool
    {
        return $this->bookableTiers()->isNotEmpty();
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
        return static::transportIconFor($this->transport_type);
    }

    /**
     * Icon for a transport type.
     *
     * Shared by the display accessor and the admin select options so both
     * always agree. Matches on substrings, so "ট্রেন + লঞ্চ" still resolves.
     */
    public static function transportIconFor(?string $transport): string
    {
        return match (true) {
            str_contains((string) $transport, 'ফ্লাই') => '✈️',
            str_contains((string) $transport, 'জিপ') => '🚙',
            str_contains((string) $transport, 'ট্রেন') => '🚆',
            str_contains((string) $transport, 'নৌকা') => '🚢',
            str_contains((string) $transport, 'লঞ্চ') => '🚢',
            default => '🚌',
        };
    }

    /**
     * The options offered by the admin transport select.
     *
     * A value already stored on the tour is kept in the list even when it is
     * not one of the suggestions, so editing a tour never silently discards
     * an unusual value.
     *
     * @return list<string>
     */
    public static function transportOptions(?string $current = null): array
    {
        $options = self::TRANSPORT_TYPES;

        if (is_string($current) && $current !== '' && ! in_array($current, $options, true)) {
            array_unshift($options, $current);
        }

        return $options;
    }
}
