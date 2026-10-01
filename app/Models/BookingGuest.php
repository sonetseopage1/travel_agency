<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One child travelling on a booking.
 *
 * Adults are only counted, so only children need a stored row: their ages
 * differ, and the age is what decides whether they pay in full, at a reduced
 * rate, or nothing at all. `unit_price` and `line_total` are snapshotted at
 * booking time so a later change to a tier's rates never rewrites history.
 */
class BookingGuest extends Model
{
    /**
     * @var list<string>
     */
    public const TYPES = ['child', 'infant'];

    protected $fillable = [
        'booking_id',
        'age',
        'type',
        'unit_price',
        'line_total',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function getIsFreeAttribute(): bool
    {
        return (float) $this->line_total <= 0;
    }
}
