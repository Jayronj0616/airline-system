<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingAddOn extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'passenger_id',
        'type',
        'description',
        'price',
        'quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the booking for this add-on.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the passenger for this add-on (if applicable).
     */
    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }

    /**
     * Get total price (price * quantity).
     */
    public function getTotalPriceAttribute()
    {
        return $this->price * $this->quantity;
    }

    /**
     * Fixed prices for add-ons.
     */
    public static function getPrices()
    {
        return [
            'baggage' => [
                '15kg' => 1500.00,
                '20kg' => 2000.00,
                '25kg' => 2500.00,
                '30kg' => 3000.00,
            ],
            'meal' => [
                'standard' => 500.00,
                'vegetarian' => 500.00,
                'vegan' => 500.00,
                'halal' => 500.00,
                'kosher' => 500.00,
                'gluten_free' => 600.00,
                'child_meal' => 400.00,
            ],
            'seat_upgrade' => [
                'extra_legroom' => 1000.00,
                'exit_row' => 800.00,
                'front_row' => 600.00,
            ],
            'insurance' => 800.00,
            'priority_boarding' => 500.00,
            'lounge_access' => 2000.00,
        ];
    }
}
