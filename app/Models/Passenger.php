<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Passenger extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_id',
        'seat_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'passport_number',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Get the booking this passenger belongs to.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the seat assigned to this passenger.
     */
    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }

    /**
     * Get full name.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
