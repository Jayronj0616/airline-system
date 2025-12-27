<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DemandController extends Controller
{
    /**
     * Display demand analytics dashboard.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $flightId = $request->input('flight_id');
        $days = $request->input('days', 7); // Default 7 days
        
        // Get all upcoming flights for the filter dropdown
        $flights = Flight::where('departure_time', '>', now())
            ->orderBy('departure_time')
            ->get();
        
        // Get selected flight or default to first flight
        $selectedFlight = $flightId 
            ? Flight::find($flightId) 
            : $flights->first();
        
        // If no flights exist, return empty view
        if (!$selectedFlight) {
            return view('admin.demand.index', [
                'flights' => collect(),
                'selectedFlight' => null,
                'demandData' => [],
                'priceData' => [],
                'flightStats' => [],
            ]);
        }
        
        // Get demand trend data (last N days)
        $demandData = $this->getDemandTrendData($selectedFlight->id, $days);
        
        // Get price correlation data
        $priceData = $this->getPriceCorrelationData($selectedFlight->id, $days);
        
        // Get overall flight statistics
        $flightStats = $this->getFlightStatistics($selectedFlight);
        
        return view('admin.demand.index', compact(
            'flights',
            'selectedFlight',
            'demandData',
            'priceData',
            'flightStats',
            'days'
        ));
    }
    
    /**
     * Get demand trend data for charting.
     */
    private function getDemandTrendData($flightId, $days)
    {
        $startDate = now()->subDays($days);
        
        // Get price history records with demand factors
        $records = PriceHistory::where('flight_id', $flightId)
            ->where('recorded_at', '>=', $startDate)
            ->orderBy('recorded_at')
            ->select(
                DB::raw('DATE(recorded_at) as date'),
                DB::raw('AVG(demand_factor) as avg_demand'),
                DB::raw('MIN(demand_factor) as min_demand'),
                DB::raw('MAX(demand_factor) as max_demand')
            )
            ->groupBy('date')
            ->get();
        
        // Format for Chart.js
        return [
            'labels' => $records->pluck('date')->map(fn($d) => date('M d', strtotime($d)))->toArray(),
            'avgDemand' => $records->pluck('avg_demand')->map(fn($v) => round($v, 2))->toArray(),
            'minDemand' => $records->pluck('min_demand')->map(fn($v) => round($v, 2))->toArray(),
            'maxDemand' => $records->pluck('max_demand')->map(fn($v) => round($v, 2))->toArray(),
        ];
    }
    
    /**
     * Get price vs demand correlation data.
     */
    private function getPriceCorrelationData($flightId, $days)
    {
        $startDate = now()->subDays($days);
        
        // Get economy class price history with demand
        $records = PriceHistory::where('flight_id', $flightId)
            ->where('recorded_at', '>=', $startDate)
            ->where('fare_class_id', 1) // Economy class
            ->orderBy('recorded_at')
            ->select(
                DB::raw('DATE(recorded_at) as date'),
                DB::raw('AVG(price) as avg_price'),
                DB::raw('AVG(demand_factor) as avg_demand')
            )
            ->groupBy('date')
            ->get();
        
        return [
            'labels' => $records->pluck('date')->map(fn($d) => date('M d', strtotime($d)))->toArray(),
            'prices' => $records->pluck('avg_price')->map(fn($v) => round($v, 2))->toArray(),
            'demand' => $records->pluck('avg_demand')->map(fn($v) => round($v, 2))->toArray(),
        ];
    }
    
    /**
     * Get overall flight statistics.
     */
    private function getFlightStatistics($flight)
    {
        $currentDemandScore = $flight->demand_score;
        $demandLevel = $flight->demand_level;
        $demandMultiplier = $flight->getDemandMultiplier();
        
        // Get latest price records
        $latestPrices = PriceHistory::where('flight_id', $flight->id)
            ->orderBy('recorded_at', 'desc')
            ->take(3)
            ->get()
            ->groupBy('fare_class_id');
        
        return [
            'current_demand_score' => round($currentDemandScore, 2),
            'demand_level' => ucfirst($demandLevel),
            'demand_multiplier' => $demandMultiplier,
            'load_factor' => $flight->load_factor,
            'booked_seats' => $flight->booked_seats_count,
            'total_seats' => $flight->aircraft->total_seats,
            'days_until_departure' => $flight->days_until_departure,
            'latest_prices' => $latestPrices,
        ];
    }
}
