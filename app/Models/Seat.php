<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Seat extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_id',
        'fare_class_id',
        'seat_number',
        'status',
        'held_at',
        'hold_expires_at',
    ];

    protected $casts = [
        'held_at' => 'datetime',
        'hold_expires_at' => 'datetime',
    ];

    /**
     * Get the flight this seat belongs to.
     */
    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    /**
     * Get the fare class of this seat.
     */
    public function fareClass()
    {
        return $this->belongsTo(FareClass::class);
    }

    /**
     * Get the passenger assigned to this seat.
     */
    public function passenger()
    {
        return $this->hasOne(Passenger::class);
    }

    /**
     * Check if seat hold has expired.
     */
    public function isHoldExpired()
    {
        if ($this->status !== 'held') {
            return false;
        }

        return $this->hold_expires_at && Carbon::now()->greaterThan($this->hold_expires_at);
    }

    /**
     * Release expired hold.
     */
    public function releaseExpiredHold()
    {
        if ($this->isHoldExpired()) {
            $this->update([
                'status' => 'available',
                'held_at' => null,
                'hold_expires_at' => null,
            ]);
            return true;
        }
        return false;
    }

    /**
     * Hold the seat for booking.
     */
    public function hold($minutes = 15)
    {
        $this->update([
            'status' => 'held',
            'held_at' => Carbon::now(),
            'hold_expires_at' => Carbon::now()->addMinutes($minutes),
        ]);
    }

    /**
     * Book the seat (confirm booking).
     */
    public function book()
    {
        $this->update([
            'status' => 'booked',
            'held_at' => null,
            'hold_expires_at' => null,
        ]);
    }

    /**
     * Release the seat (make available again).
     */
    public function release()
    {
        $this->update([
            'status' => 'available',
            'held_at' => null,
            'hold_expires_at' => null,
        ]);
    }

    /**
     * Scope: Get available seats.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope: Get held seats.
     */
    public function scopeHeld($query)
    {
        return $query->where('status', 'held');
    }

    /**
     * Scope: Get booked seats.
     */
    public function scopeBooked($query)
    {
        return $query->where('status', 'booked');
    }
}
