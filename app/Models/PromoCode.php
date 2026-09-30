<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'max_discount',
        'usage_limit',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_type' => 'string',
            'discount_value' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Tours this promo is limited to. An empty relation means "all tours".
     */
    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class, 'promo_code_tour');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isExpired(): bool
    {
        return $this->valid_until !== null && $this->valid_until->isPast();
    }

    public function hasStarted(): bool
    {
        return $this->valid_from !== null && $this->valid_from->isFuture();
    }

    public function isUsedUp(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }

    public function appliesToTour(int $tourId): bool
    {
        $tourIds = $this->relationLoaded('tours')
            ? $this->tours->pluck('id')
            // Qualify the column: the pivot join makes a bare "id" ambiguous.
            : $this->tours()->pluck('tours.id');

        return $tourIds->isEmpty() || $tourIds->contains($tourId);
    }

    /**
     * Discount amount for a given subtotal. Never returns a negative value,
     * so a fixed discount cannot push the total below zero.
     */
    public function discountFor(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        $discount = $this->discount_type === 'percentage'
            ? $subtotal * ((float) $this->discount_value / 100)
            : (float) $this->discount_value;

        if ($this->discount_type === 'percentage' && $this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($discount, $subtotal), 2);
    }

    /**
     * Why this promo cannot be used, or null when it is valid.
     */
    public function rejectionReason(?int $tourId = null): ?string
    {
        if (! $this->is_active) {
            return 'এই প্রোমো কোডটি নিষ্ক্রিয় করা হয়েছে।';
        }

        if ($this->hasStarted()) {
            return 'এই প্রোমো কোডটি '.$this->valid_from->format('d M Y').' তারিখ থেকে কার্যকর হবে।';
        }

        if ($this->isExpired()) {
            return 'এই প্রোমো কোডটি '.$this->valid_until->format('d M Y').' তারিখে শেষ হয়েছে।';
        }

        if ($this->isUsedUp()) {
            return 'এই প্রোমো কোডের ব্যবহার সীমা পূর্ণ হয়েছে।';
        }

        if ($tourId !== null && ! $this->appliesToTour($tourId)) {
            return 'এই প্রোমো কোডটি এই ট্যুরে প্রযোজ্য নয়।';
        }

        return null;
    }

    public function isUsableFor(?int $tourId = null): bool
    {
        return $this->rejectionReason($tourId) === null;
    }

    public function describeDiscount(): string
    {
        return $this->discount_type === 'percentage'
            ? rtrim(rtrim((string) $this->discount_value, '0'), '.').'% ছাড়'
            : '৳'.number_format((float) $this->discount_value).' ছাড়';
    }
}
