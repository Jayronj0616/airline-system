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
        'gender',
        'nationality',
        'passport_number',
        'meal_preference',
        'baggage_allowance',
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

    /**
     * Get check-in record for this passenger.
     */
    public function checkIn()
    {
        return $this->hasOne(CheckIn::class);
    }

    /**
     * Get boarding pass for this passenger.
     */
    public function boardingPass()
    {
        return $this->hasOne(BoardingPass::class);
    }

    /**
     * Check if passenger has checked in.
     */
    public function hasCheckedIn()
    {
        return $this->checkIn()->exists();
    }

    /**
     * Check if passenger has boarding pass.
     */
    public function hasBoardingPass()
    {
        return $this->boardingPass()->exists();
    }
}
