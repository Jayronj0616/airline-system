<?php

namespace App\Services;

use App\Models\Flight;
use App\Models\FareClass;
use App\Models\User;
use App\Models\Seat;
use App\Models\Booking;
use Illuminate\Support\Collection;

class InventoryService
{
    protected $bookingHoldService;
    protected $pricingService;
    protected $overbookingService;

    public function __construct(
        BookingHoldService $bookingHoldService, 
        PricingService $pricingService,
        OverbookingService $overbookingService
    ) {
        $this->bookingHoldService = $bookingHoldService;
        $this->pricingService = $pricingService;
        $this->overbookingService = $overbookingService;
    }

    /**
     * Get available seats for a specific flight and fare class.
     * Considers overbooking - can exceed physical capacity.
     * 
     * @param Flight $flight
     * @param FareClass $fareClass
     * @return int Number of available seats (can be virtual)
     */
    public function getAvailableSeats(Flight $flight, FareClass $fareClass): int
    {
        // Get total physical seats for this fare class
        $totalPhysicalSeats = Seat::where('flight_id', $flight->id)
            ->where('fare_class_id', $fareClass->id)
            ->count();

        // Calculate virtual capacity based on overbooking settings
        $virtualCapacity = $totalPhysicalSeats;
        
        if ($this->overbookingService->canOverbook($flight)) {
            // Use the LOWER of flight's percentage or fare class max percentage
            $effectivePercentage = min(
                $flight->overbooking_percentage, 
                $fareClass->max_overbooking_percentage
            );

            // Calculate virtual capacity for this fare class
            $virtualCapacity = (int) floor($totalPhysicalSeats * (1 + $effectivePercentage / 100));
        }

        // Get count of all bookings (confirmed + held) for this fare class
        $bookedCount = Booking::where('flight_id', $flight->id)
            ->where('fare_class_id', $fareClass->id)
            ->whereIn('status', ['confirmed', 'held', 'checked_in'])
            ->sum('seat_count');

        // Calculate available (virtual capacity - booked)
        $available = $virtualCapacity - $bookedCount;

        return max(0, $available);
    }

    /**
     * Get detailed seat availability for all fare classes on a flight.
     * 
     * @param Flight $flight
     * @return array
     */
    public function getFlightAvailability(Flight $flight): array
    {
        $fareClasses = FareClass::all();
        $availability = [];

        foreach ($fareClasses as $fareClass) {
            $total = Seat::where('flight_id', $flight->id)
                ->where('fare_class_id', $fareClass->id)
                ->count();

            $available = $this->getAvailableSeats($flight, $fareClass);
            
            $held = Seat::where('flight_id', $flight->id)
                ->where('fare_class_id', $fareClass->id)
                ->where('status', 'held')
                ->count();

            $booked = Seat::where('flight_id', $flight->id)
                ->where('fare_class_id', $fareClass->id)
                ->where('status', 'booked')
                ->count();

            $availability[$fareClass->id] = [
                'fare_class' => $fareClass,
                'total' => $total,
                'available' => $available,
                'held' => $held,
                'booked' => $booked,
                'availability_percent' => $total > 0 ? round(($available / $total) * 100, 2) : 0,
            ];
        }

        return $availability;
    }

    /**
     * Hold seats for a user (creates booking hold).
     * Now supports overbooking - may create bookings without physical seat assignment.
     * 
     * @param User $user
     * @param Flight $flight
     * @param FareClass $fareClass
     * @param int $count Number of seats to hold
     * @return Booking
     * @throws \Exception
     */
    public function holdSeats(User $user, Flight $flight, FareClass $fareClass, int $count): Booking
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($user, $flight, $fareClass, $count) {
            // Check virtual capacity (considers overbooking)
            $available = $this->getAvailableSeats($flight, $fareClass);
            
            if ($available < $count) {
                throw new \Exception("Only {$available} seat(s) available in {$fareClass->name} class. You requested {$count}.");
            }

            // Calculate and lock current price
            $currentPrice = $this->pricingService->calculateCurrentPrice($flight, $fareClass);
            
            if ($currentPrice === null) {
                throw new \Exception("Unable to calculate price. Flight may be sold out or departed.");
            }

            // Create the hold using BookingHoldService
            $booking = $this->bookingHoldService->createHold($user, $flight, $fareClass, $count, $currentPrice);

            // Update overbooked count if applicable
            if ($this->overbookingService->canOverbook($flight)) {
                $this->overbookingService->updateOverbookedCount($flight);
            }

            return $booking;
        });
    }

    /**
     * Release all expired holds (background job wrapper).
     * Uses chunking for memory efficiency with large datasets.
     * 
     * @return array Statistics about released holds
     */
    public function releaseExpiredHolds(): array
    {
        $stats = [
            'found' => 0,
            'released' => 0,
            'seats_freed' => 0,
            'errors' => 0,
        ];

        // Use chunking to process in batches (memory efficient)
        Booking::expiredHolds()->chunkById(100, function ($expiredBookings) use (&$stats) {
            foreach ($expiredBookings as $booking) {
                $stats['found']++;
                
                try {
                    $seatCount = $booking->seat_count;
                    $this->bookingHoldService->releaseHold($booking);
                    
                    $stats['released']++;
                    $stats['seats_freed'] += $seatCount;
                } catch (\Exception $e) {
                    $stats['errors']++;
                    \Log::error("Failed to release expired hold: {$booking->booking_reference}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        return $stats;
    }

    /**
     * Confirm a booking (moves from HELD to CONFIRMED).
     * Updates overbooking count and tracks demand.
     * 
     * @param Booking $booking
     * @return bool
     * @throws \Exception
     */
    public function confirmBooking(Booking $booking): bool
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($booking) {
            // Track booking demand when booking is confirmed (Phase 5 Task 2)
            $booking->flight->increaseBookingDemand();
            
            $confirmed = $this->bookingHoldService->confirmHold($booking);

            // Update overbooked count after confirmation
            if ($confirmed && $this->overbookingService->canOverbook($booking->flight)) {
                $this->overbookingService->updateOverbookedCount($booking->flight);
            }

            return $confirmed;
        });
    }

    /**
     * Check if a flight has enough capacity for requested seats.
     * 
     * @param Flight $flight
     * @param FareClass $fareClass
     * @param int $requestedSeats
     * @return bool
     */
    public function hasCapacity(Flight $flight, FareClass $fareClass, int $requestedSeats): bool
    {
        return $this->getAvailableSeats($flight, $fareClass) >= $requestedSeats;
    }

    /**
     * Get inventory summary for a flight.
     * Now includes overbooking statistics.
     * 
     * @param Flight $flight
     * @return array
     */
    public function getInventorySummary(Flight $flight): array
    {
        $totalSeats = $flight->aircraft->total_seats;
        
        $available = Seat::where('flight_id', $flight->id)
            ->where('status', 'available')
            ->count();

        $held = Seat::where('flight_id', $flight->id)
            ->where('status', 'held')
            ->count();

        $booked = Seat::where('flight_id', $flight->id)
            ->where('status', 'booked')
            ->count();

        // Get overbooking stats
        $overbookingStats = $this->overbookingService->getOverbookingStats($flight);

        return [
            'total_seats' => $totalSeats,
            'available' => $available,
            'held' => $held,
            'booked' => $booked,
            'load_factor' => $totalSeats > 0 ? round(($booked / $totalSeats) * 100, 2) : 0,
            'utilization' => $totalSeats > 0 ? round((($booked + $held) / $totalSeats) * 100, 2) : 0,
            'overbooking' => $overbookingStats,
        ];
    }

    /**
     * Get seats that are about to expire (within specified minutes).
     * 
     * @param int $withinMinutes Default 5 minutes
     * @return Collection
     */
    public function getSeatsExpiringSoon(int $withinMinutes = 5): Collection
    {
        $threshold = now()->addMinutes($withinMinutes);

        return Seat::where('status', 'held')
            ->where('hold_expires_at', '>', now())
            ->where('hold_expires_at', '<=', $threshold)
            ->with(['flight', 'fareClass'])
            ->get();
    }

    /**
     * Get bookings about to expire (within specified minutes).
     * 
     * @param int $withinMinutes Default 5 minutes
     * @return Collection
     */
    public function getBookingsExpiringSoon(int $withinMinutes = 5): Collection
    {
        $threshold = now()->addMinutes($withinMinutes);

        return Booking::where('status', 'held')
            ->where('hold_expires_at', '>', now())
            ->where('hold_expires_at', '<=', $threshold)
            ->with(['user', 'flight', 'fareClass'])
            ->get();
    }

    /**
     * Get all available seats (actual Seat models) for selection.
     * 
     * @param Flight $flight
     * @param FareClass $fareClass
     * @return Collection
     */
    public function getAvailableSeatList(Flight $flight, FareClass $fareClass): Collection
    {
        return Seat::where('flight_id', $flight->id)
            ->where('fare_class_id', $fareClass->id)
            ->where('status', 'available')
            ->orderBy('seat_number')
            ->get();
    }

    /**
     * Check if specific seat is available.
     * 
     * @param Flight $flight
     * @param string $seatNumber
     * @return bool
     */
    public function isSeatAvailable(Flight $flight, string $seatNumber): bool
    {
        return Seat::where('flight_id', $flight->id)
            ->where('seat_number', $seatNumber)
            ->where('status', 'available')
            ->exists();
    }

    /**
     * Get seat map data for visualization.
     * Groups seats by row and column.
     * 
     * @param Flight $flight
     * @return array
     */
    public function getSeatMap(Flight $flight): array
    {
        $seats = Seat::where('flight_id', $flight->id)
            ->with('fareClass')
            ->orderBy('seat_number')
            ->get();

        $seatMap = [];

        foreach ($seats as $seat) {
            // Extract row number and column letter from seat_number (e.g., "12A")
            preg_match('/^(\d+)([A-Z])$/', $seat->seat_number, $matches);
            
            if (count($matches) === 3) {
                $row = (int) $matches[1];
                $column = $matches[2];

                if (!isset($seatMap[$row])) {
                    $seatMap[$row] = [];
                }

                $seatMap[$row][$column] = [
                    'seat_id' => $seat->id,
                    'seat_number' => $seat->seat_number,
                    'status' => $seat->status,
                    'fare_class' => $seat->fareClass->name,
                    'fare_class_code' => $seat->fareClass->code,
                ];
            }
        }

        ksort($seatMap); // Sort by row number

        return $seatMap;
    }

    /**
     * Get inventory statistics across all flights.
     * 
     * @return array
     */
    public function getSystemInventoryStats(): array
    {
        $totalSeats = Seat::count();
        $availableSeats = Seat::where('status', 'available')->count();
        $heldSeats = Seat::where('status', 'held')->count();
        $bookedSeats = Seat::where('status', 'booked')->count();

        return [
            'total_seats' => $totalSeats,
            'available' => $availableSeats,
            'held' => $heldSeats,
            'booked' => $bookedSeats,
            'available_percent' => $totalSeats > 0 ? round(($availableSeats / $totalSeats) * 100, 2) : 0,
            'utilization_percent' => $totalSeats > 0 ? round((($bookedSeats + $heldSeats) / $totalSeats) * 100, 2) : 0,
            'active_bookings' => Booking::where('status', 'held')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
        ];
    }
}
