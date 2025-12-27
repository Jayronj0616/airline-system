<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\FareClass;
use App\Models\User;
use App\Models\Seat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingHoldService
{
    /**
     * Hold duration in minutes.
     */
    const HOLD_DURATION = 15;

    /**
     * Create a booking hold without seat assignment.
     * Seats will be assigned only when booking is confirmed.
     * Handles unique constraint violations with retry logic.
     * 
     * @param User $user
     * @param Flight $flight
     * @param FareClass $fareClass
     * @param int $seatCount Number of seats to book
     * @param float $lockedPrice Price per seat (locked from PricingService)
     * @return Booking
     * @throws \Exception
     */
    public function createHold(User $user, Flight $flight, FareClass $fareClass, int $seatCount, float $lockedPrice): Booking
    {
        $maxAttempts = 3;
        $lastException = null;
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return DB::transaction(function () use ($user, $flight, $fareClass, $seatCount, $lockedPrice) {
                    // Create the booking hold (no seat assignment yet)
                    $booking = Booking::create([
                        'user_id' => $user->id,
                        'flight_id' => $flight->id,
                        'fare_class_id' => $fareClass->id,
                        'status' => 'held',
                        'locked_price' => $lockedPrice,
                        'total_price' => $lockedPrice * $seatCount,
                        'seat_count' => $seatCount,
                        'held_at' => Carbon::now(),
                        'hold_expires_at' => Carbon::now()->addMinutes(self::HOLD_DURATION),
                    ]);

                    return $booking;
                });
            } catch (\Illuminate\Database\QueryException $e) {
                // Check if this is a unique constraint violation (error code 23000)
                if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'booking_reference')) {
                    $lastException = $e;
                    // Retry with new reference
                    continue;
                }
                // Other database errors should be thrown immediately
                throw $e;
            }
        }
        
        // If we exhausted all retry attempts
        throw new \Exception(
            "Failed to create booking after {$maxAttempts} attempts due to reference collision",
            0,
            $lastException
        );
    }

    /**
     * Release a booking hold.
     * 
     * @param Booking $booking
     * @return bool
     */
    public function releaseHold(Booking $booking): bool
    {
        if ($booking->status !== 'held') {
            return false;
        }

        return DB::transaction(function () use ($booking) {
            // Mark booking as expired (no seats to release at hold stage)
            $booking->update(['status' => 'expired']);

            return true;
        });
    }

    /**
     * Confirm a booking hold and assign seats (after successful payment).
     * 
     * @param Booking $booking
     * @return bool
     * @throws \Exception
     */
    public function confirmHold(Booking $booking): bool
    {
        if ($booking->status !== 'held') {
            throw new \Exception("Cannot confirm booking with status: {$booking->status}");
        }

        if ($booking->isHoldExpired()) {
            throw new \Exception("Booking hold has expired");
        }

        return DB::transaction(function () use ($booking) {
            // Assign seats if available (may be overbooked)
            $this->assignSeatsToBooking($booking);

            // Mark booking as confirmed
            $booking->confirm();

            return true;
        });
    }

    /**
     * Check if user has any active holds for the same flight.
     * Prevents duplicate holds.
     * 
     * @param User $user
     * @param Flight $flight
     * @return bool
     */
    public function hasActiveHold(User $user, Flight $flight): bool
    {
        return Booking::where('user_id', $user->id)
            ->where('flight_id', $flight->id)
            ->where('status', 'held')
            ->where('hold_expires_at', '>', Carbon::now())
            ->exists();
    }

    /**
     * Get remaining time for a booking hold.
     * 
     * @param Booking $booking
     * @return int Minutes remaining (0 if expired)
     */
    public function getRemainingTime(Booking $booking): int
    {
        if ($booking->status !== 'held' || !$booking->hold_expires_at) {
            return 0;
        }

        $remaining = Carbon::now()->diffInMinutes($booking->hold_expires_at, false);
        
        return max(0, $remaining);
    }

    /**
     * Extend a booking hold (admin function).
     * 
     * @param Booking $booking
     * @param int $additionalMinutes
     * @return bool
     */
    public function extendHold(Booking $booking, int $additionalMinutes = 15): bool
    {
        if ($booking->status !== 'held') {
            return false;
        }

        $newExpiry = Carbon::now()->addMinutes($additionalMinutes);
        
        return DB::transaction(function () use ($booking, $newExpiry) {
            $booking->update(['hold_expires_at' => $newExpiry]);
            return true;
        });
    }

    /**
     * Get statistics about booking holds.
     * 
     * @return array
     */
    public function getHoldStatistics(): array
    {
        return [
            'active_holds' => Booking::where('status', 'held')
                ->where('hold_expires_at', '>', Carbon::now())
                ->count(),
            'expired_holds' => Booking::expiredHolds()->count(),
            'holds_expiring_soon' => Booking::where('status', 'held')
                ->where('hold_expires_at', '>', Carbon::now())
                ->where('hold_expires_at', '<', Carbon::now()->addMinutes(5))
                ->count(),
        ];
    }

    /**
     * Assign physical seats to passengers when booking is confirmed.
     * If overbooked, some passengers may not get seat assignments.
     * 
     * @param Booking $booking
     * @return void
     */
    protected function assignSeatsToBooking(Booking $booking): void
    {
        // Get available physical seats for this fare class
        $availableSeats = Seat::where('flight_id', $booking->flight_id)
            ->where('fare_class_id', $booking->fare_class_id)
            ->where('status', 'available')
            ->lockForUpdate()
            ->limit($booking->seat_count)
            ->get();

        // Assign seats to as many passengers as possible
        $seatIndex = 0;
        foreach ($booking->passengers as $passenger) {
            if ($seatIndex < $availableSeats->count()) {
                $seat = $availableSeats[$seatIndex];
                
                // Update passenger with seat assignment
                $passenger->update(['seat_id' => $seat->id]);
                
                // Mark seat as booked
                $seat->book();
                
                $seatIndex++;
            }
            // If no seats available, passenger remains unassigned (seat_id = null)
        }
    }
}
