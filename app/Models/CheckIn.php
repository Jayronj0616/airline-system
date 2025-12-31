<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'passenger_id',
        'checked_in_at',
        'check_in_method',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    /**
     * Get the booking for this check-in.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the passenger for this check-in.
     */
    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }
}
