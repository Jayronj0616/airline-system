<?php

namespace App\Services;

use App\Models\Flight;

class OverbookingService
{
    /**
     * Maximum allowed overbooking percentage (industry standard).
     */
    const MAX_OVERBOOKING_PERCENTAGE = 15;

    /**
     * Hours before departure to disable overbooking.
     */
    const DISABLE_HOURS_BEFORE_DEPARTURE = 48;

    /**
     * Days before departure required to enable overbooking.
     */
    const MIN_DAYS_FOR_OVERBOOKING = 7;

    /**
     * Get the physical capacity of a flight.
     * 
     * @param Flight $flight
     * @return int
     */
    public function getPhysicalCapacity(Flight $flight): int
    {
        return $flight->aircraft->total_seats;
    }

    /**
     * Get the virtual capacity of a flight (physical + overbooking allowance).
     * 
     * @param Flight $flight
     * @return int
     */
    public function getVirtualCapacity(Flight $flight): int
    {
        $physicalCapacity = $this->getPhysicalCapacity($flight);
        
        // If overbooking is not enabled or not allowed, return physical capacity
        if (!$this->canOverbook($flight)) {
            return $physicalCapacity;
        }
        
        // Calculate virtual capacity with overbooking percentage
        $overbookingMultiplier = 1 + ($flight->overbooking_percentage / 100);
        $virtualCapacity = (int) floor($physicalCapacity * $overbookingMultiplier);
        
        return $virtualCapacity;
    }

    /**
     * Check if a flight can currently accept overbooking.
     * 
     * @param Flight $flight
     * @return bool
     */
    public function canOverbook(Flight $flight): bool
    {
        // Overbooking must be enabled for this flight
        if (!$flight->overbooking_enabled) {
            return false;
        }

        // Flight must be more than MIN_DAYS away
        $daysUntilDeparture = $flight->days_until_departure;
        if ($daysUntilDeparture < self::MIN_DAYS_FOR_OVERBOOKING) {
            return false;
        }

        // Flight must be more than DISABLE_HOURS away
        $hoursUntilDeparture = $flight->hours_until_departure;
        if ($hoursUntilDeparture < self::DISABLE_HOURS_BEFORE_DEPARTURE) {
            return false;
        }

        // Flight must not be departed or cancelled
        if (in_array($flight->status, ['departed', 'arrived', 'cancelled'])) {
            return false;
        }

        return true;
    }

    /**
     * Calculate how many seats are overbooked.
     * 
     * @param Flight $flight
     * @return int
     */
    public function getOverbookedCount(Flight $flight): int
    {
        $confirmedBookings = $this->getConfirmedBookingsCount($flight);
        $physicalCapacity = $this->getPhysicalCapacity($flight);
        
        $overbooked = $confirmedBookings - $physicalCapacity;
        
        return max(0, $overbooked);
    }

    /**
     * Get available seats for booking (virtual capacity - confirmed bookings).
     * 
     * @param Flight $flight
     * @return int
     */
    public function getAvailableSeats(Flight $flight): int
    {
        $virtualCapacity = $this->getVirtualCapacity($flight);
        $confirmedBookings = $this->getConfirmedBookingsCount($flight);
        
        $available = $virtualCapacity - $confirmedBookings;
        
        return max(0, $available);
    }

    /**
     * Get count of confirmed bookings for a flight.
     * 
     * @param Flight $flight
     * @return int
     */
    public function getConfirmedBookingsCount(Flight $flight): int
    {
        return $flight->bookings()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();
    }

    /**
     * Calculate load factor (confirmed bookings / physical capacity).
     * 
     * @param Flight $flight
     * @return float
     */
    public function getLoadFactor(Flight $flight): float
    {
        $physicalCapacity = $this->getPhysicalCapacity($flight);
        
        if ($physicalCapacity == 0) {
            return 0;
        }
        
        $confirmedBookings = $this->getConfirmedBookingsCount($flight);
        
        return round(($confirmedBookings / $physicalCapacity) * 100, 2);
    }

    /**
     * Check if flight has reached virtual capacity.
     * 
     * @param Flight $flight
     * @return bool
     */
    public function isVirtualCapacityReached(Flight $flight): bool
    {
        return $this->getAvailableSeats($flight) <= 0;
    }

    /**
     * Check if flight is at risk of denied boarding.
     * Risk exists when confirmed bookings exceed physical capacity.
     * 
     * @param Flight $flight
     * @return bool
     */
    public function isAtRiskOfDeniedBoarding(Flight $flight): bool
    {
        return $this->getOverbookedCount($flight) > 0;
    }

    /**
     * Enable overbooking for a flight.
     * 
     * @param Flight $flight
     * @param float $percentage
     * @return bool
     */
    public function enableOverbooking(Flight $flight, float $percentage = 10.0): bool
    {
        // Validate percentage
        if ($percentage < 0 || $percentage > self::MAX_OVERBOOKING_PERCENTAGE) {
            return false;
        }

        // Check if flight is eligible
        if ($flight->days_until_departure < self::MIN_DAYS_FOR_OVERBOOKING) {
            return false;
        }

        $flight->update([
            'overbooking_enabled' => true,
            'overbooking_percentage' => $percentage,
        ]);

        return true;
    }

    /**
     * Disable overbooking for a flight.
     * 
     * @param Flight $flight
     * @return void
     */
    public function disableOverbooking(Flight $flight): void
    {
        $flight->update([
            'overbooking_enabled' => false,
        ]);
    }

    /**
     * Update the overbooked count for a flight.
     * 
     * @param Flight $flight
     * @return void
     */
    public function updateOverbookedCount(Flight $flight): void
    {
        $overbookedCount = $this->getOverbookedCount($flight);
        
        $flight->update([
            'overbooked_count' => $overbookedCount,
        ]);
    }

    /**
     * Get overbooking statistics for a flight.
     * 
     * @param Flight $flight
     * @return array
     */
    public function getOverbookingStats(Flight $flight): array
    {
        $physicalCapacity = $this->getPhysicalCapacity($flight);
        $virtualCapacity = $this->getVirtualCapacity($flight);
        $confirmedBookings = $this->getConfirmedBookingsCount($flight);
        $availableSeats = $this->getAvailableSeats($flight);
        $overbookedCount = $this->getOverbookedCount($flight);
        $loadFactor = $this->getLoadFactor($flight);

        return [
            'physical_capacity' => $physicalCapacity,
            'virtual_capacity' => $virtualCapacity,
            'confirmed_bookings' => $confirmedBookings,
            'available_seats' => $availableSeats,
            'overbooked_count' => $overbookedCount,
            'load_factor' => $loadFactor,
            'overbooking_enabled' => $flight->overbooking_enabled,
            'overbooking_percentage' => $flight->overbooking_percentage,
            'can_overbook' => $this->canOverbook($flight),
            'at_risk' => $this->isAtRiskOfDeniedBoarding($flight),
            'virtual_capacity_reached' => $this->isVirtualCapacityReached($flight),
        ];
    }

    /**
     * Validate overbooking percentage.
     * 
     * @param float $percentage
     * @return bool
     */
    public function isValidOverbookingPercentage(float $percentage): bool
    {
        return $percentage >= 0 && $percentage <= self::MAX_OVERBOOKING_PERCENTAGE;
    }

    /**
     * Calculate recommended overbooking percentage based on no-show probability.
     * Uses fare class no-show rates to determine safe overbooking levels.
     * 
     * @param Flight $flight
     * @return float
     */
    public function calculateRecommendedOverbooking(Flight $flight): float
    {
        // Get all fare classes for this flight
        $fareClasses = \App\Models\FareClass::all();
        
        $totalSeats = 0;
        $weightedNoShowSum = 0;
        
        foreach ($fareClasses as $fareClass) {
            $seatsInClass = \App\Models\Seat::where('flight_id', $flight->id)
                ->where('fare_class_id', $fareClass->id)
                ->count();
            
            if ($seatsInClass > 0) {
                $totalSeats += $seatsInClass;
                $weightedNoShowSum += $seatsInClass * $fareClass->no_show_probability;
            }
        }
        
        if ($totalSeats == 0) {
            return 0;
        }
        
        // Calculate weighted average no-show probability
        $avgNoShowProbability = $weightedNoShowSum / $totalSeats;
        
        // Use 80% of no-show probability as safe overbooking percentage
        // This leaves a safety margin
        $recommendedPercentage = $avgNoShowProbability * 0.8;
        
        // Cap at maximum allowed
        return min($recommendedPercentage, self::MAX_OVERBOOKING_PERCENTAGE);
    }

    /**
     * Calculate expected no-shows for a flight.
     * 
     * @param Flight $flight
     * @return float
     */
    public function calculateExpectedNoShows(Flight $flight): float
    {
        $fareClasses = \App\Models\FareClass::all();
        $expectedNoShows = 0;
        
        foreach ($fareClasses as $fareClass) {
            $confirmedBookings = \App\Models\Booking::where('flight_id', $flight->id)
                ->where('fare_class_id', $fareClass->id)
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->sum('seat_count');
            
            $expectedNoShows += $confirmedBookings * ($fareClass->no_show_probability / 100);
        }
        
        return round($expectedNoShows, 2);
    }

    /**
     * Calculate denied boarding risk level.
     * Returns risk assessment based on overbooking vs expected no-shows.
     * 
     * @param Flight $flight
     * @return array ['risk_level' => string, 'risk_score' => float, 'message' => string]
     */
    public function calculateDeniedBoardingRisk(Flight $flight): array
    {
        $overbookedCount = $this->getOverbookedCount($flight);
        
        if ($overbookedCount == 0) {
            return [
                'risk_level' => 'none',
                'risk_score' => 0,
                'message' => 'Flight is not overbooked. No risk of denied boarding.',
            ];
        }
        
        $expectedNoShows = $this->calculateExpectedNoShows($flight);
        
        // Risk score: overbooked seats minus expected no-shows
        $riskScore = $overbookedCount - $expectedNoShows;
        
        if ($riskScore <= 0) {
            return [
                'risk_level' => 'low',
                'risk_score' => round($riskScore, 2),
                'message' => "Low risk. Expected {$expectedNoShows} no-shows vs {$overbookedCount} overbooked.",
            ];
        } elseif ($riskScore <= 3) {
            return [
                'risk_level' => 'medium',
                'risk_score' => round($riskScore, 2),
                'message' => "Medium risk. Possible {$riskScore} denied boardings.",
            ];
        } else {
            return [
                'risk_level' => 'high',
                'risk_score' => round($riskScore, 2),
                'message' => "High risk! Likely {$riskScore} denied boardings.",
            ];
        }
    }

    /**
     * Flag flight as overbooked if confirmed bookings exceed physical capacity.
     * This should be called at departure time or when checking in passengers.
     * 
     * @param Flight $flight
     * @return bool True if flight needs manual resolution
     */
    public function flagOverbookedFlight(Flight $flight): bool
    {
        // Only flag if at departure time or within boarding window
        $hoursUntilDeparture = $flight->hours_until_departure;
        
        if ($hoursUntilDeparture > 2) {
            return false; // Too early to flag
        }
        
        $overbookedCount = $this->getOverbookedCount($flight);
        
        if ($overbookedCount <= 0) {
            return false; // Not overbooked
        }
        
        // Flight is overbooked and needs resolution
        // Update flight status if not already flagged
        if ($flight->status === 'scheduled') {
            $flight->update(['status' => 'boarding']);
        }
        
        return true;
    }

    /**
     * Create denied boarding records for passengers that need to be bumped.
     * This is called manually by admin when resolving overbooking.
     * 
     * @param Flight $flight
     * @param array $bookingIds Array of booking IDs to deny
     * @param string $notes Optional notes
     * @return int Number of denied boarding records created
     */
    public function createDeniedBoardingRecords(Flight $flight, array $bookingIds, string $notes = ''): int
    {
        $count = 0;
        
        foreach ($bookingIds as $bookingId) {
            $booking = \App\Models\Booking::find($bookingId);
            
            if (!$booking || $booking->flight_id !== $flight->id) {
                continue;
            }
            
            \App\Models\DeniedBoarding::create([
                'flight_id' => $flight->id,
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'fare_class_id' => $booking->fare_class_id,
                'resolution_type' => 'pending',
                'compensation_amount' => 0,
                'notes' => $notes,
                'denied_at' => now(),
            ]);
            
            $count++;
        }
        
        return $count;
    }
}
