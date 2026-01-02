<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_reference',
        'user_id',
        'contact_email',
        'contact_phone',
        'contact_name',
        'flight_id',
        'fare_class_id',
        'status',
        'locked_price',
        'total_price',
        'seat_count',
        'held_at',
        'hold_expires_at',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'locked_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'seat_count' => 'integer',
        'held_at' => 'datetime',
        'hold_expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Boot method to auto-generate booking reference.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = self::generateBookingReference();
            }
        });
    }

    /**
     * Generate unique booking reference (6-9 alphanumeric characters).
     * Uses retry logic to handle race conditions with unique constraint.
     * Format: XXX123YYZ (mix of letters and numbers)
     * 
     * @param int $maxAttempts Maximum retry attempts
     * @return string
     * @throws \Exception If unable to generate unique reference after max attempts
     */
    protected static function generateBookingReference(int $maxAttempts = 10): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Exclude confusing chars: I, O, 0, 1
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            // Generate 6-9 random alphanumeric characters
            $length = rand(6, 9);
            $reference = '';
            
            for ($i = 0; $i < $length; $i++) {
                $reference .= $characters[rand(0, strlen($characters) - 1)];
            }
            
            // Ensure it contains both letters and numbers for better uniqueness
            if (!preg_match('/[A-Z]/', $reference) || !preg_match('/[0-9]/', $reference)) {
                continue; // Regenerate if missing letters or numbers
            }
            
            // Check uniqueness (including soft deleted records)
            if (!self::withTrashed()->where('booking_reference', $reference)->exists()) {
                return $reference;
            }
        }
        
        // If we couldn't generate a unique reference after max attempts, throw exception
        throw new \Exception("Unable to generate unique booking reference after {$maxAttempts} attempts");
    }

    /**
     * Get the user who made this booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the flight for this booking.
     */
    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    /**
     * Get booking logs.
     */
    public function logs()
    {
        return $this->hasMany(BookingLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the fare class for this booking.
     */
    public function fareClass()
    {
        return $this->belongsTo(FareClass::class);
    }

    /**
     * Get all passengers for this booking.
     */
    public function passengers()
    {
        return $this->hasMany(Passenger::class);
    }

    /**
     * Get all add-ons for this booking.
     */
    public function addOns()
    {
        return $this->hasMany(BookingAddOn::class);
    }

    /**
     * Get check-in records for this booking.
     */
    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }

    /**
     * Get boarding passes for this booking.
     */
    public function boardingPasses()
    {
        return $this->hasMany(BoardingPass::class);
    }

    /**
     * Check if all passengers have checked in.
     */
    public function isCheckedIn()
    {
        return $this->checkIns()->count() === $this->passengers()->count();
    }

    /**
     * Get total add-ons price.
     */
    public function getAddOnsTotalAttribute()
    {
        return $this->addOns()->sum(\DB::raw('price * quantity'));
    }

    /**
     * Get grand total (booking + add-ons).
     */
    public function getGrandTotalAttribute()
    {
        return $this->total_price + $this->add_ons_total;
    }

    // ==========================================
    // State Checking Methods
    // ==========================================

    /**
     * Check if booking is in DRAFT state.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if booking is in HELD state.
     */
    public function isHeld(): bool
    {
        return $this->status === 'held';
    }

    /**
     * Check if booking is in CONFIRMED_UNPAID state.
     */
    public function isConfirmedUnpaid(): bool
    {
        return $this->status === 'confirmed_unpaid';
    }

    /**
     * Check if booking is in CONFIRMED_PAID state.
     */
    public function isConfirmedPaid(): bool
    {
        return $this->status === 'confirmed_paid';
    }

    /**
     * Check if booking is in any CONFIRMED state.
     */
    public function isConfirmed(): bool
    {
        return in_array($this->status, ['confirmed_unpaid', 'confirmed_paid']);
    }

    /**
     * Check if booking is in CANCELLED state.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if booking is in EXPIRED state.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    /**
     * Check if booking hold has expired.
     */
    public function isHoldExpired()
    {
        if ($this->status !== 'held') {
            return false;
        }

        return $this->hold_expires_at && Carbon::now()->greaterThan($this->hold_expires_at);
    }

    /**
     * Check if booking can be confirmed.
     */
    public function canBeConfirmed(): bool
    {
        return $this->status === 'held' && !$this->isHoldExpired();
    }

    /**
     * Check if booking can be expired.
     */
    public function canBeExpired(): bool
    {
        return $this->status === 'held';
    }

    // ==========================================
    // State Transition Methods
    // ==========================================

    /**
     * Create a hold (typically called from BookingHoldService).
     * This method is mainly for documentation - actual creation happens via Booking::create().
     * 
     * @param User $user
     * @param Flight $flight
     * @param FareClass $fareClass
     * @param int $seatCount
     * @param float $lockedPrice
     * @return self
     */
    public static function hold(User $user, Flight $flight, FareClass $fareClass, int $seatCount, float $lockedPrice): self
    {
        return self::create([
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'fare_class_id' => $fareClass->id,
            'status' => 'held',
            'locked_price' => $lockedPrice,
            'total_price' => $lockedPrice * $seatCount,
            'seat_count' => $seatCount,
            'held_at' => Carbon::now(),
            'hold_expires_at' => Carbon::now()->addMinutes(15),
        ]);
    }

    /**
     * Expire this booking.
     * Only valid for HELD bookings.
     * 
     * @throws \Exception
     */
    public function expire()
    {
        if ($this->status !== 'held') {
            throw new \Exception("Cannot expire booking with status: {$this->status}. Only held bookings can expire.");
        }

        $this->update(['status' => 'expired']);
    }

    /**
     * Confirm this booking (payment successful).
     * Only valid for HELD bookings that haven't expired.
     * Seat assignment happens in BookingHoldService.
     * Email sent asynchronously via queue.
     * 
     * @throws \Exception
     */
    public function confirm()
    {
        if ($this->status !== 'held') {
            throw new \Exception("Cannot confirm booking with status: {$this->status}. Only held bookings can be confirmed.");
        }

        if ($this->isHoldExpired()) {
            throw new \Exception("Cannot confirm expired booking. Hold expired at {$this->hold_expires_at}.");
        }

        if ($this->flight->isPast()) {
            throw new \Exception("Cannot confirm booking for departed flight.");
        }

        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => Carbon::now(),
        ]);

        // Queue confirmation email (non-blocking)
        \Mail::to($this->user->email)->queue(new \App\Mail\BookingConfirmed($this));
    }

    /**
     * Cancel this booking.
     * Valid for HELD and CONFIRMED bookings only.
     * Email sent asynchronously via queue.
     * 
     * @throws \Exception
     */
    public function cancel($reason = null)
    {
        if (!in_array($this->status, ['held', 'confirmed'])) {
            throw new \Exception("Cannot cancel booking with status: {$this->status}. Only held or confirmed bookings can be cancelled.");
        }

        if ($this->status === 'confirmed' && $this->flight->isPast()) {
            throw new \Exception("Cannot cancel booking for departed flight.");
        }

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now(),
            'cancellation_reason' => $reason,
        ]);

        // Release assigned seats if any
        foreach ($this->passengers as $passenger) {
            if ($passenger->seat) {
                $passenger->seat->release();
            }
        }

        // Queue cancellation email (non-blocking)
        \Mail::to($this->user->email)->queue(new \App\Mail\BookingCancelled($this));
    }

    /**
     * Check if booking can be cancelled based on fare rules.
     */
    public function canBeCancelled()
    {
        // Can't cancel if already cancelled or expired
        if (in_array($this->status, ['cancelled', 'expired'])) {
            return false;
        }

        // Check if flight has departed
        if ($this->flight->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Scope: Get active bookings (held or confirmed).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['held', 'confirmed']);
    }

    /**
     * Scope: Get expired holds.
     */
    public function scopeExpiredHolds($query)
    {
        return $query->where('status', 'held')
            ->where('hold_expires_at', '<', Carbon::now());
    }
}
