<?php

namespace App\Services;

use App\Models\Flight;
use App\Models\FareClass;
use App\Models\PriceHistory;
use Carbon\Carbon;

class PricingService
{
    /**
     * Calculate current price for a flight and fare class.
     * 
     * @param Flight $flight
     * @param FareClass $fareClass
     * @return float|null Returns null if flight is past or sold out
     */
    public function calculateCurrentPrice(Flight $flight, FareClass $fareClass): ?float
    {
        // Check if flight has departed
        if ($flight->isPast()) {
            return null;
        }

        // Get base fare based on fare class
        $baseFare = $this->getBaseFare($flight, $fareClass);
        
        if ($baseFare === null || $baseFare <= 0) {
            return null;
        }

        // Check if fare class is sold out
        $availableSeats = $flight->availableSeatsForFareClass($fareClass->id);
        if ($availableSeats <= 0) {
            return null;
        }

        // Calculate all multipliers
        $timeFactor = $this->getTimeFactor($flight);
        $inventoryFactor = $this->getInventoryFactor($flight, $fareClass);
        $demandFactor = $this->getDemandFactor($flight);

        // Final price calculation
        $finalPrice = $baseFare * $timeFactor * $inventoryFactor * $demandFactor;

        return round($finalPrice, 2);
    }

    /**
     * Get base fare for a specific fare class.
     * 
     * @param Flight $flight
     * @param FareClass $fareClass
     * @return float|null
     */
    protected function getBaseFare(Flight $flight, FareClass $fareClass): ?float
    {
        return match(strtolower($fareClass->code)) {
            'y', 'economy' => $flight->base_price_economy,
            'j', 'business' => $flight->base_price_business,
            'f', 'first' => $flight->base_price_first,
            default => null,
        };
    }

    /**
     * Calculate time factor based on days before departure.
     * 
     * 0-7 days: 2.0x
     * 8-14 days: 1.5x
     * 15-30 days: 1.2x
     * 30+ days: 1.0x
     * 
     * @param Flight $flight
     * @return float
     */
    public function getTimeFactor(Flight $flight): float
    {
        $daysUntilDeparture = $flight->days_until_departure;

        return match(true) {
            $daysUntilDeparture < 0 => 2.0, // Already departed or same day
            $daysUntilDeparture <= 7 => 2.0,
            $daysUntilDeparture <= 14 => 1.5,
            $daysUntilDeparture <= 30 => 1.2,
            default => 1.0,
        };
    }

    /**
     * Calculate inventory factor based on seat availability percentage.
     * 
     * <10% available: 1.8x
     * 10-30% available: 1.4x
     * 30-60% available: 1.1x
     * 60%+ available: 1.0x
     * 
     * @param Flight $flight
     * @param FareClass $fareClass
     * @return float
     */
    public function getInventoryFactor(Flight $flight, FareClass $fareClass): float
    {
        $totalSeats = $flight->seats()->where('fare_class_id', $fareClass->id)->count();
        
        if ($totalSeats === 0) {
            return 1.0;
        }

        $availableSeats = $flight->availableSeatsForFareClass($fareClass->id);
        $availabilityPercent = ($availableSeats / $totalSeats) * 100;

        return match(true) {
            $availabilityPercent < 10 => 1.8,
            $availabilityPercent < 30 => 1.4,
            $availabilityPercent < 60 => 1.1,
            default => 1.0,
        };
    }

    /**
     * Calculate demand factor based on flight's demand score.
     * 
     * High demand (80-100): 1.5x
     * Medium demand (40-79): 1.2x
     * Low demand (0-39): 1.0x
     * 
     * @param Flight $flight
     * @return float
     */
    public function getDemandFactor(Flight $flight): float
    {
        $demandScore = $flight->demand_score ?? 50;

        return match(true) {
            $demandScore >= 80 => 1.5,
            $demandScore >= 40 => 1.2,
            default => 1.0,
        };
    }

    /**
     * Record price change to price_history table.
     * 
     * @param Flight $flight
     * @param FareClass $fareClass
     * @param float $price
     * @param array $factors Additional factors used in calculation
     * @return PriceHistory
     */
    public function recordPriceHistory(Flight $flight, FareClass $fareClass, float $price, array $factors = []): PriceHistory
    {
        return PriceHistory::create([
            'flight_id' => $flight->id,
            'fare_class_id' => $fareClass->id,
            'price' => $price,
            'factors_used' => array_merge([
                'time_factor' => $this->getTimeFactor($flight),
                'inventory_factor' => $this->getInventoryFactor($flight, $fareClass),
                'demand_factor' => $this->getDemandFactor($flight),
            ], $factors),
        ]);
    }

    /**
     * Calculate and update prices for all fare classes on a flight.
     * Only records to history if price actually changed.
     * 
     * @param Flight $flight
     * @return array [fare_class_id => ['price' => float, 'changed' => bool]]
     */
    public function updateFlightPrices(Flight $flight): array
    {
        $prices = [];
        $fareClasses = FareClass::all();

        foreach ($fareClasses as $fareClass) {
            $newPrice = $this->calculateCurrentPrice($flight, $fareClass);
            
            if ($newPrice === null) {
                continue;
            }

            // Get latest price from history
            $latestPrice = $this->getLatestPrice($flight, $fareClass);
            
            // Only record if price changed or no history exists
            $priceChanged = false;
            if (!$latestPrice || abs($latestPrice->price - $newPrice) > 0.01) {
                $this->recordPriceHistory($flight, $fareClass, $newPrice);
                $priceChanged = true;
            }
            
            $prices[$fareClass->id] = [
                'price' => $newPrice,
                'changed' => $priceChanged,
            ];
        }

        return $prices;
    }

    /**
     * Get latest price for a flight and fare class from history.
     * 
     * @param Flight $flight
     * @param FareClass $fareClass
     * @return PriceHistory|null
     */
    public function getLatestPrice(Flight $flight, FareClass $fareClass): ?PriceHistory
    {
        return PriceHistory::where('flight_id', $flight->id)
            ->where('fare_class_id', $fareClass->id)
            ->latest()
            ->first();
    }

    /**
     * Get price trend indicator for a flight and fare class.
     * Compares current price with price from 1 hour ago.
     * 
     * @param Flight $flight
     * @param FareClass $fareClass
     * @return string '↑' (up), '↓' (down), '→' (stable)
     */
    public function getPriceTrend(Flight $flight, FareClass $fareClass): string
    {
        $currentPrice = $this->getLatestPrice($flight, $fareClass);
        
        if (!$currentPrice) {
            return '→';
        }

        $previousPrice = PriceHistory::where('flight_id', $flight->id)
            ->where('fare_class_id', $fareClass->id)
            ->where('created_at', '<', Carbon::now()->subHour())
            ->latest()
            ->first();

        if (!$previousPrice) {
            return '→';
        }

        if ($currentPrice->price > $previousPrice->price) {
            return '↑';
        } elseif ($currentPrice->price < $previousPrice->price) {
            return '↓';
        }

        return '→';
    }
}
