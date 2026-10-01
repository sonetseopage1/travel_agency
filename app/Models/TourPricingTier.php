<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One pricing tier of a tour: single, couple, family or group.
 *
 * The tier owns every pricing lever the operator can set, plus the cabin
 * inventory it consumes. All money is derived in TourPricingService so the
 * browser and the server can never disagree.
 */
class TourPricingTier extends Model
{
    /**
     * Tier types, in the order an operator is offered them.
     *
     * @var list<string>
     */
    public const TYPES = ['single', 'couple', 'family', 'group'];

    /**
     * @var list<string>
     */
    public const DISCOUNT_TYPES = ['none', 'percent', 'fixed'];

    /**
     * Guard against a nonsensical age.
     */
    public const MAX_AGE = 18;

    protected $fillable = [
        'tour_id',
        'type',
        'label',
        'price_per_adult',
        'min_adults',
        'max_adults',
        'infant_age_max',
        'child_age_max',
        'child_price_percent',
        'capacity_per_cabin',
        'included_cabin_count',
        'extra_cabin_fee',
        'discount_type',
        'discount_value',
        'cabins_total',
        'cabins_booked',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_per_adult' => 'decimal:2',
            'min_adults' => 'integer',
            'max_adults' => 'integer',
            'infant_age_max' => 'integer',
            'child_age_max' => 'integer',
            'child_price_percent' => 'decimal:2',
            'capacity_per_cabin' => 'integer',
            'included_cabin_count' => 'integer',
            'extra_cabin_fee' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'cabins_total' => 'integer',
            'cabins_booked' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Human label, falling back to a readable form of the type.
     */
    public function getDisplayNameAttribute(): string
    {
        if (! empty($this->label)) {
            return $this->label;
        }

        return ucfirst($this->type);
    }

    /**
     * Rate charged to one guest of the given age.
     *
     * The boundaries follow the operator's wording: "below 3" and "below 8"
     * are strict, so a guest aged exactly `infant_age_max` or exactly
     * `child_age_max` is no longer free or reduced.
     */
    public function unitPriceForAge(?int $age): float
    {
        $adult = (float) $this->price_per_adult;

        if ($age === null) {
            return $adult;
        }

        if ($age < (int) $this->infant_age_max) {
            return 0.0;
        }

        if ($age < (int) $this->child_age_max) {
            return round($adult * ((float) $this->child_price_percent / 100), 2);
        }

        return $adult;
    }

    /**
     * Which band an age falls into.
     */
    public function guestTypeForAge(?int $age): string
    {
        if ($age !== null && $age < (int) $this->infant_age_max) {
            return 'infant';
        }

        if ($age !== null && $age < (int) $this->child_age_max) {
            return 'child';
        }

        return 'adult';
    }

    /**
     * Cabins remaining, or null when the operator is not tracking them.
     */
    public function getCabinsAvailableAttribute(): ?int
    {
        if ($this->cabins_total === null) {
            return null;
        }

        return max(0, (int) $this->cabins_total - (int) $this->cabins_booked);
    }

    /**
     * True when a booking needing $cabins cabins can still be accepted.
     */
    public function hasCabinAvailability(int $cabins): bool
    {
        $available = $this->cabins_available;

        return $available === null || $available >= $cabins;
    }
}
