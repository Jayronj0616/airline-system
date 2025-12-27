<?php

namespace App\Services;

use App\Models\Flight;
use Carbon\Carbon;

class DemandPatternService
{
    /**
     * Popular routes with higher base demand.
     * Key: route (origin-destination), Value: popularity score (0-30)
     */
    private array $popularRoutes = [
        'LAX-JFK' => 25, // LA to NYC - very popular
        'JFK-LAX' => 25,
        'JFK-MIA' => 20, // NYC to Miami - popular vacation route
        'MIA-JFK' => 18,
        'SFO-SEA' => 15, // SF to Seattle - business route
        'SEA-SFO' => 15,
        'LAX-ORD' => 18, // LA to Chicago
        'ORD-LAX' => 18,
        'DFW-DEN' => 10, // Dallas to Denver - moderate
        'DEN-DFW' => 10,
    ];

    /**
     * Holiday periods with surge demand.
     * Format: [start_date, end_date, surge_amount]
     */
    private array $holidayPeriods = [
        // Christmas/New Year (Dec 20 - Jan 5)
        ['12-20', '01-05', 20],
        // Thanksgiving week (Nov 20-28)
        ['11-20', '11-28', 15],
        // Summer travel season (Jun 15 - Aug 15)
        ['06-15', '08-15', 10],
        // Spring break (Mar 10-30)
        ['03-10', '03-30', 12],
        // Labor Day weekend (Sep 1-5)
        ['09-01', '09-05', 8],
        // Memorial Day weekend (May 25-30)
        ['05-25', '05-30', 8],
    ];

    /**
     * Calculate realistic demand score for a flight.
     * 
     * @param Flight $flight
     * @return float Demand score (0-100)
     */
    public function calculateRealisticDemand(Flight $flight): float
    {
        $baseScore = 50; // Start at medium demand
        
        // Factor 1: Popular routes
        $baseScore += $this->getRoutePopularityBonus($flight);
        
        // Factor 2: Weekend flights
        $baseScore += $this->getWeekendBonus($flight);
        
        // Factor 3: Holiday periods
        $baseScore += $this->getHolidayBonus($flight);
        
        // Factor 4: Red-eye flights (penalty)
        $baseScore -= $this->getRedEyePenalty($flight);
        
        // Factor 5: Time of day preference
        $baseScore += $this->getTimeOfDayBonus($flight);
        
        // Ensure score stays within 0-100 range
        return max(0, min(100, $baseScore));
    }

    /**
     * Get bonus for popular routes.
     * 
     * @param Flight $flight
     * @return float
     */
    private function getRoutePopularityBonus(Flight $flight): float
    {
        $routeKey = $flight->origin . '-' . $flight->destination;
        return $this->popularRoutes[$routeKey] ?? 0;
    }

    /**
     * Get bonus for weekend flights (Fri-Sun).
     * 
     * @param Flight $flight
     * @return float
     */
    private function getWeekendBonus(Flight $flight): float
    {
        $dayOfWeek = $flight->departure_time->dayOfWeek;
        
        // Friday: +10
        if ($dayOfWeek === Carbon::FRIDAY) {
            return 10;
        }
        
        // Saturday/Sunday: +15
        if ($dayOfWeek === Carbon::SATURDAY || $dayOfWeek === Carbon::SUNDAY) {
            return 15;
        }
        
        // Thursday (pre-weekend): +5
        if ($dayOfWeek === Carbon::THURSDAY) {
            return 5;
        }
        
        return 0;
    }

    /**
     * Get bonus for holiday periods.
     * 
     * @param Flight $flight
     * @return float
     */
    private function getHolidayBonus(Flight $flight): float
    {
        $flightDate = $flight->departure_time;
        
        foreach ($this->holidayPeriods as [$startDate, $endDate, $surgeAmount]) {
            $start = Carbon::parse($flightDate->year . '-' . $startDate);
            $end = Carbon::parse($flightDate->year . '-' . $endDate);
            
            // Handle year boundary (e.g., Dec-Jan)
            if ($end->lt($start)) {
                $end->addYear();
            }
            
            if ($flightDate->between($start, $end)) {
                return $surgeAmount;
            }
        }
        
        return 0;
    }

    /**
     * Get penalty for red-eye flights (10pm - 5am).
     * 
     * @param Flight $flight
     * @return float
     */
    private function getRedEyePenalty(Flight $flight): float
    {
        $hour = $flight->departure_time->hour;
        
        // Red-eye hours: 10pm (22) to 5am (5)
        if ($hour >= 22 || $hour <= 5) {
            return 15; // -15 points for red-eye
        }
        
        // Late evening (8pm-10pm): slight penalty
        if ($hour >= 20 && $hour < 22) {
            return 5;
        }
        
        // Very early morning (5am-7am): slight penalty
        if ($hour > 5 && $hour < 7) {
            return 5;
        }
        
        return 0;
    }

    /**
     * Get bonus for preferred departure times.
     * 
     * @param Flight $flight
     * @return float
     */
    private function getTimeOfDayBonus(Flight $flight): float
    {
        $hour = $flight->departure_time->hour;
        
        // Peak travel times (7am-9am, 5pm-7pm): +8
        if (($hour >= 7 && $hour <= 9) || ($hour >= 17 && $hour <= 19)) {
            return 8;
        }
        
        // Good travel times (9am-12pm, 2pm-5pm): +5
        if (($hour > 9 && $hour < 12) || ($hour >= 14 && $hour < 17)) {
            return 5;
        }
        
        // Midday (12pm-2pm): neutral
        if ($hour >= 12 && $hour < 14) {
            return 0;
        }
        
        return 0;
    }

    /**
     * Apply realistic demand patterns to a flight.
     * Updates the flight's demand_score.
     * 
     * @param Flight $flight
     * @return void
     */
    public function applyRealisticDemand(Flight $flight): void
    {
        $demandScore = $this->calculateRealisticDemand($flight);
        $flight->update(['demand_score' => $demandScore]);
    }

    /**
     * Apply realistic demand patterns to multiple flights.
     * 
     * @param \Illuminate\Support\Collection $flights
     * @return void
     */
    public function applyRealisticDemandBatch($flights): void
    {
        foreach ($flights as $flight) {
            $this->applyRealisticDemand($flight);
        }
    }

    /**
     * Get demand pattern breakdown for analysis.
     * 
     * @param Flight $flight
     * @return array
     */
    public function getDemandBreakdown(Flight $flight): array
    {
        return [
            'base_score' => 50,
            'route_popularity' => $this->getRoutePopularityBonus($flight),
            'weekend_bonus' => $this->getWeekendBonus($flight),
            'holiday_bonus' => $this->getHolidayBonus($flight),
            'red_eye_penalty' => -$this->getRedEyePenalty($flight),
            'time_of_day_bonus' => $this->getTimeOfDayBonus($flight),
            'final_score' => $this->calculateRealisticDemand($flight),
        ];
    }
}
