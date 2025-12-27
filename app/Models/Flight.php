<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Flight extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_number',
        'aircraft_id',
        'origin',
        'destination',
        'departure_time',
        'arrival_time',
        'status',
        'base_price_economy',
        'base_price_business',
        'base_price_first',
        'demand_score',
        'overbooking_enabled',
        'overbooking_percentage',
        'overbooked_count',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'base_price_economy' => 'integer',
        'base_price_business' => 'integer',
        'base_price_first' => 'integer',
        'demand_score' => 'decimal:2',
        'overbooking_enabled' => 'boolean',
        'overbooking_percentage' => 'decimal:2',
        'overbooked_count' => 'integer',
    ];

    /**
     * Get the aircraft for this flight.
     */
    public function aircraft()
    {
        return $this->belongsTo(Aircraft::class);
    }

    /**
     * Get all seats for this flight.
     */
    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    /**
     * Get all bookings for this flight.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get price history for this flight.
     */
    public function priceHistory()
    {
        return $this->hasMany(PriceHistory::class);
    }

    /**
     * Get search history for this flight.
     */
    public function searches()
    {
        return $this->hasMany(FlightSearch::class);
    }

    /**
     * Get denied boarding events for this flight.
     */
    public function deniedBoardings()
    {
        return $this->hasMany(DeniedBoarding::class);
    }

    /**
     * Get days until departure.
     */
    public function getDaysUntilDepartureAttribute()
    {
        return Carbon::now()->diffInDays($this->departure_time, false);
    }

    /**
     * Get hours until departure.
     */
    public function getHoursUntilDepartureAttribute()
    {
        return Carbon::now()->diffInHours($this->departure_time, false);
    }

    /**
     * Check if flight is in the past.
     */
    public function isPast()
    {
        return $this->departure_time->isPast();
    }

    /**
     * Get available seat count for a specific fare class.
     */
    public function availableSeatsForFareClass($fareClassId)
    {
        return $this->seats()
            ->where('fare_class_id', $fareClassId)
            ->where('status', 'available')
            ->count();
    }

    /**
     * Get total booked seats count.
     */
    public function getBookedSeatsCountAttribute()
    {
        return $this->seats()->where('status', 'booked')->count();
    }

    /**
     * Get load factor (percentage of seats booked).
     */
    public function getLoadFactorAttribute()
    {
        $totalSeats = $this->aircraft->total_seats;
        if ($totalSeats == 0) return 0;
        
        return round(($this->booked_seats_count / $totalSeats) * 100, 2);
    }

    // ==========================================
    // Demand Score Management (Phase 5)
    // ==========================================

    /**
     * Increase demand score based on search activity.
     * Called when a user searches for this flight.
     * 
     * @param float $increase Amount to increase (default: random 0.5-2.0)
     * @return void
     */
    public function increaseSearchDemand(float $increase = null)
    {
        if ($increase === null) {
            $increase = rand(50, 200) / 100; // Random 0.5 to 2.0
        }

        // Use atomic increment with cap at 100
        $this->increment('demand_score', $increase);
        
        // Cap at 100
        if ($this->demand_score > 100) {
            $this->update(['demand_score' => 100]);
        }
    }

    /**
     * Increase demand score based on booking activity.
     * Called when a booking is confirmed for this flight.
     * 
     * @param float $increase Amount to increase (default: random 3.0-5.0)
     * @return void
     */
    public function increaseBookingDemand(float $increase = null)
    {
        if ($increase === null) {
            $increase = rand(300, 500) / 100; // Random 3.0 to 5.0
        }

        // Use atomic increment with cap at 100
        $this->increment('demand_score', $increase);
        
        // Cap at 100
        if ($this->demand_score > 100) {
            $this->update(['demand_score' => 100]);
        }
    }

    /**
     * Decrease demand score due to time decay.
     * Called periodically (hourly) when there's no activity.
     * 
     * @param float $decrease Amount to decrease (default: random 1.0-3.0)
     * @return void
     */
    public function decayDemand(float $decrease = null)
    {
        if ($decrease === null) {
            $decrease = rand(100, 300) / 100; // Random 1.0 to 3.0
        }

        // Use atomic decrement with floor at 0
        $this->decrement('demand_score', $decrease);
        
        // Floor at 0
        if ($this->demand_score < 0) {
            $this->update(['demand_score' => 0]);
        }
    }

    /**
     * Apply departure proximity boost to demand.
     * Flights within 7 days get increased demand.
     * Called by scheduled command or pricing service.
     * 
     * @return void
     */
    public function applyDepartureProximityBoost()
    {
        $daysUntil = $this->days_until_departure;
        
        // Only apply to future flights
        if ($daysUntil < 0) {
            return;
        }
        
        // Apply boost based on days until departure
        $boost = 0;
        
        if ($daysUntil <= 1) {
            // 24 hours or less: +10 points
            $boost = 10;
        } elseif ($daysUntil <= 3) {
            // 2-3 days: +7 points
            $boost = 7;
        } elseif ($daysUntil <= 7) {
            // 4-7 days: +5 points
            $boost = 5;
        }
        
        if ($boost > 0) {
            // Use atomic increment with cap at 100
            $this->increment('demand_score', $boost);
            
            // Cap at 100
            if ($this->demand_score > 100) {
                $this->update(['demand_score' => 100]);
            }
        }
    }

    /**
     * Get departure proximity multiplier for pricing.
     * Returns additional multiplier based on how close to departure.
     * 
     * @return float
     */
    public function getDepartureProximityMultiplier()
    {
        $daysUntil = $this->days_until_departure;
        
        // Past flights: no multiplier
        if ($daysUntil < 0) {
            return 1.0;
        }
        
        // Apply multiplier based on days until departure
        if ($daysUntil <= 1) {
            return 1.3; // 30% increase for last-minute bookings
        } elseif ($daysUntil <= 3) {
            return 1.2; // 20% increase for 2-3 days out
        } elseif ($daysUntil <= 7) {
            return 1.1; // 10% increase for within a week
        }
        
        return 1.0; // No increase for >7 days out
    }

    /**
     * Set demand score to a specific value.
     * Ensures it stays within 0-100 range.
     * 
     * @param float $score New demand score
     * @return void
     */
    public function setDemandScore(float $score)
    {
        $score = max(0, min(100, $score));
        $this->update(['demand_score' => $score]);
    }

    /**
     * Get demand level category.
     * 
     * @return string 'low', 'medium', or 'high'
     */
    public function getDemandLevelAttribute()
    {
        if ($this->demand_score >= 80) {
            return 'high';
        } elseif ($this->demand_score >= 40) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get demand multiplier for pricing.
     * High demand: 1.5x
     * Medium demand: 1.2x
     * Low demand: 1.0x
     * 
     * @return float
     */
    public function getDemandMultiplier()
    {
        if ($this->demand_score >= 80) {
            return 1.5;
        } elseif ($this->demand_score >= 40) {
            return 1.2;
        } else {
            return 1.0;
        }
    }

    // ==========================================
    // Overbooking Methods (Phase 6)
    // ==========================================

    /**
     * Get physical capacity (actual seats on aircraft).
     * 
     * @return int
     */
    public function getPhysicalCapacityAttribute()
    {
        return $this->aircraft->total_seats;
    }

    /**
     * Get virtual capacity (with overbooking allowance).
     * 
     * @return int
     */
    public function getVirtualCapacityAttribute()
    {
        $service = app(\App\Services\OverbookingService::class);
        return $service->getVirtualCapacity($this);
    }

    /**
     * Get count of confirmed bookings.
     * 
     * @return int
     */
    public function getConfirmedBookingsCountAttribute()
    {
        return $this->bookings()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();
    }

    /**
     * Check if flight can accept overbooking.
     * 
     * @return bool
     */
    public function canOverbook()
    {
        $service = app(\App\Services\OverbookingService::class);
        return $service->canOverbook($this);
    }

    /**
     * Check if flight is at risk of denied boarding.
     * 
     * @return bool
     */
    public function isAtRiskOfDeniedBoarding()
    {
        $service = app(\App\Services\OverbookingService::class);
        return $service->isAtRiskOfDeniedBoarding($this);
    }
}
