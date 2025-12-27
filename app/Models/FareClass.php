<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FareClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'max_overbooking_percentage',
        'no_show_probability',
    ];

    protected $casts = [
        'max_overbooking_percentage' => 'decimal:2',
        'no_show_probability' => 'decimal:2',
    ];

    /**
     * Get the fare rule for this fare class.
     */
    public function fareRule()
    {
        return $this->hasOne(FareRule::class);
    }

    /**
     * Get all seats in this fare class.
     */
    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    /**
     * Get all bookings in this fare class.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get price history for this fare class.
     */
    public function priceHistory()
    {
        return $this->hasMany(PriceHistory::class);
    }
}
