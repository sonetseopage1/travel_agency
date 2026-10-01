<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    /**
     * Valid booking states. Shared by the admin filter, the status dropdowns
     * and the validation rules so all three always agree.
     *
     * @var list<string>
     */
    public const STATUSES = ['pending', 'confirmed', 'cancelled', 'completed'];

    /**
     * Valid payment states.
     *
     * `refunded` is included because cancelled bookings are marked as such,
     * and the admin filter has to be able to select it.
     *
     * @var list<string>
     */
    public const PAYMENT_STATUSES = ['unpaid', 'partial', 'paid', 'refunded'];

    protected $fillable = [
        'tour_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'guest_count',
        'pricing_tier_id',
        'pricing_tier_type',
        'pricing_tier_label',
        'adult_rate',
        'adult_count',
        'child_count',
        'infant_count',
        'cabin_count',
        'extra_cabin_amount',
        'tier_discount_amount',
        'subtotal',
        'discount_amount',
        'promo_code',
        'promo_code_id',
        'total_price',
        'status',
        'payment_status',
        'payment_method',
        'transaction_id',
        'special_notes',
        'admin_note',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'guest_count' => 'integer',
            'adult_count' => 'integer',
            'child_count' => 'integer',
            'infant_count' => 'integer',
            'cabin_count' => 'integer',
            'adult_rate' => 'decimal:2',
            'extra_cabin_amount' => 'decimal:2',
            'tier_discount_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function pricingTier(): BelongsTo
    {
        return $this->belongsTo(TourPricingTier::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * True once an admin has approved the booking.
     *
     * A submitted booking is only an application, so the receipt is withheld
     * until this is true. A cancelled booking never counts as approved, even if
     * it was approved before being cancelled.
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->approved_at !== null && $this->status !== 'cancelled';
    }

    /**
     * The token that identifies this booking in a receipt link.
     *
     * Bookings created before the token column existed get one on first use,
     * so an old link never dead-ends.
     */
    public function receiptToken(): string
    {
        if (empty($this->receipt_token)) {
            $this->forceFill(['receipt_token' => bin2hex(random_bytes(32))])->save();
        }

        return $this->receipt_token;
    }

    /**
     * The URL the admin shares once the booking is approved.
     */
    public function receiptUrl(): string
    {
        return route('bookings.receipt', ['token' => $this->receiptToken()]);
    }

    /**
     * The children on this booking. Adults are counted, not listed.
     */
    public function guests(): HasMany
    {
        return $this->hasMany(BookingGuest::class)->orderBy('sort_order')->orderBy('id');
    }

    public function getHasDiscountAttribute(): bool
    {
        // Either lever counts as a discount: the operator's tier discount or a
        // promo code on top of it.
        return (float) $this->discount_amount > 0 || (float) $this->tier_discount_amount > 0;
    }

    /**
     * Bookings that hold inventory: anything not cancelled.
     */
    public function scopeOccupying(Builder $query): Builder
    {
        return $query->where('status', '!=', 'cancelled');
    }
}
