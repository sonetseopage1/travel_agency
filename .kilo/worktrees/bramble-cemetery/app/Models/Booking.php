<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'tour_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'guest_count',
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
            'total_price' => 'decimal:2',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }
}
