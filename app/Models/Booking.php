<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected function casts(): array
    {
        return [
            'guest_count' => 'integer',
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

    public function getHasDiscountAttribute(): bool
    {
        return (float) $this->discount_amount > 0;
    }
}
