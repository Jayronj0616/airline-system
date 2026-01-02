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
        'block_reason',
        'blocked_at',
        'blocked_by',
    ];

    protected $casts = [
        'held_at' => 'datetime',
        'hold_expires_at' => 'datetime',
        'blocked_at' => 'datetime',
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
     * Get the user who blocked this seat.
     */
    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Block seat for crew.
     */
    public function blockForCrew($userId, $reason = null)
    {
        $this->update([
            'status' => 'blocked_crew',
            'block_reason' => $reason ?? 'Reserved for crew',
            'blocked_at' => Carbon::now(),
            'blocked_by' => $userId,
        ]);
    }

    /**
     * Block seat for maintenance.
     */
    public function blockForMaintenance($userId, $reason = null)
    {
        $this->update([
            'status' => 'blocked_maintenance',
            'block_reason' => $reason ?? 'Under maintenance',
            'blocked_at' => Carbon::now(),
            'blocked_by' => $userId,
        ]);
    }

    /**
     * Release blocked seat.
     */
    public function releaseBlock()
    {
        if (in_array($this->status, ['blocked_crew', 'blocked_maintenance'])) {
            $this->update([
                'status' => 'available',
                'block_reason' => null,
                'blocked_at' => null,
                'blocked_by' => null,
            ]);
            return true;
        }
        return false;
    }

    /**
     * Check if seat is blocked.
     */
    public function isBlocked()
    {
        return in_array($this->status, ['blocked_crew', 'blocked_maintenance']);
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
