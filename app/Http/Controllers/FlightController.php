<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\FareClass;
use App\Models\FlightSearch;
use App\Services\PricingService;
use App\Services\FareRuleService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FlightController extends Controller
{
    protected $pricingService;
    protected $fareRuleService;

    public function __construct(PricingService $pricingService, FareRuleService $fareRuleService)
    {
        $this->pricingService = $pricingService;
        $this->fareRuleService = $fareRuleService;
    }

    /**
     * Display flight search page.
     */
    public function search(Request $request)
    {
        // Start with base query and immediately exclude past flights
        $query = Flight::with('aircraft')
            ->where('departure_time', '>', now());

        // Filter by origin
        if ($request->filled('origin')) {
            $query->where('origin', 'LIKE', "%{$request->origin}%");
        }

        // Filter by destination
        if ($request->filled('destination')) {
            $query->where('destination', 'LIKE', "%{$request->destination}%");
        }

        // Filter by date (only for future flights on that date)
        if ($request->filled('date')) {
            $date = Carbon::parse($request->date);
            
            // If searching for today, only show flights after current time
            if ($date->isToday()) {
                $query->where('departure_time', '>', now());
            } else {
                // For future dates, show all flights on that date
                $query->whereDate('departure_time', $date);
            }
        }
        
        // Filter by departure time of day
        if ($request->filled('time_of_day')) {
            switch ($request->time_of_day) {
                case 'morning':
                    $query->whereTime('departure_time', '>=', '06:00:00')
                          ->whereTime('departure_time', '<', '12:00:00');
                    break;
                case 'afternoon':
                    $query->whereTime('departure_time', '>=', '12:00:00')
                          ->whereTime('departure_time', '<', '18:00:00');
                    break;
                case 'evening':
                    $query->whereTime('departure_time', '>=', '18:00:00')
                          ->whereTime('departure_time', '<', '24:00:00');
                    break;
                case 'night':
                    $query->whereTime('departure_time', '>=', '00:00:00')
                          ->whereTime('departure_time', '<', '06:00:00');
                    break;
            }
        }
        
        // Filter by fare class availability
        if ($request->filled('fare_class')) {
            $fareClassId = $request->fare_class;
            $query->whereHas('seats', function($q) use ($fareClassId) {
                $q->where('fare_class_id', $fareClassId)
                  ->where('status', 'available');
            });
        }
        
        // Sort by duration or departure time
        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'price_asc':
                case 'price_desc':
                    // Will sort after price calculation
                    break;
                case 'duration_asc':
                    $query->orderByRaw('TIMESTAMPDIFF(MINUTE, departure_time, arrival_time) ASC');
                    break;
                case 'duration_desc':
                    $query->orderByRaw('TIMESTAMPDIFF(MINUTE, departure_time, arrival_time) DESC');
                    break;
                case 'departure_asc':
                    $query->orderBy('departure_time', 'asc');
                    break;
                case 'departure_desc':
                    $query->orderBy('departure_time', 'desc');
                    break;
                default:
                    $query->orderBy('departure_time');
            }
        } else {
            $query->orderBy('departure_time');
        }
        
        $flights = $query->paginate(10)->withQueryString();
        $fareClasses = FareClass::with('fareRule')->get();

        // Track search activity for demand calculation (Phase 5 Task 2 & 7)
        $searchParams = [
            'origin' => $request->input('origin'),
            'destination' => $request->input('destination'),
            'date' => $request->input('date'),
        ];
        
        foreach ($flights as $flight) {
            // Log search in flight_searches table
            FlightSearch::logSearch(
                $flight->id,
                auth()->id(),
                $searchParams,
                $request->ip()
            );
            
            // Increase demand score
            $flight->increaseSearchDemand();
        }

        // Calculate current prices for each flight and fare class
        $flightPrices = [];
        $priceTrends = [];
        $lastUpdated = [];
        $fareRules = [];

        foreach ($flights as $flight) {
            foreach ($fareClasses as $fareClass) {
                $key = "{$flight->id}_{$fareClass->id}";
                
                $flightPrices[$key] = $this->pricingService->calculateCurrentPrice($flight, $fareClass);
                $priceTrends[$key] = $this->pricingService->getPriceTrend($flight, $fareClass);
                
                $latestPrice = $this->pricingService->getLatestPrice($flight, $fareClass);
                $lastUpdated[$key] = $latestPrice ? $latestPrice->created_at->diffForHumans() : 'Never';
                
                $fareRules[$fareClass->id] = $this->fareRuleService->getRuleSummary($fareClass);
            }
        }
        
        // Store min/max prices for each flight
        $flightMinPrices = [];
        foreach ($flights as $flight) {
            $minPrice = null;
            foreach ($fareClasses as $fareClass) {
                $key = "{$flight->id}_{$fareClass->id}";
                $price = $flightPrices[$key];
                if ($price && ($minPrice === null || $price < $minPrice)) {
                    $minPrice = $price;
                }
            }
            $flightMinPrices[$flight->id] = $minPrice;
        }
        
        // Filter by price range
        if ($request->filled('price_min') || $request->filled('price_max')) {
            $flights = $flights->filter(function($flight) use ($request, $flightMinPrices) {
                $minPrice = $flightMinPrices[$flight->id] ?? null;
                
                if ($minPrice === null) return false;
                
                if ($request->filled('price_min') && $minPrice < $request->price_min) {
                    return false;
                }
                if ($request->filled('price_max') && $minPrice > $request->price_max) {
                    return false;
                }
                return true;
            });
        }
        
        // Sort by price if requested
        if ($request->filled('sort_by') && in_array($request->sort_by, ['price_asc', 'price_desc'])) {
            $flights = $flights->sort(function($a, $b) use ($flightMinPrices, $request) {
                $minPriceA = $flightMinPrices[$a->id] ?? PHP_INT_MAX;
                $minPriceB = $flightMinPrices[$b->id] ?? PHP_INT_MAX;
                
                if ($request->sort_by === 'price_asc') {
                    return $minPriceA <=> $minPriceB;
                } else {
                    return $minPriceB <=> $minPriceA;
                }
            });
        }

        return view('flights.search', compact(
            'flights',
            'fareClasses',
            'flightPrices',
            'priceTrends',
            'lastUpdated',
            'fareRules',
            'flightMinPrices'
        ));
    }

    /**
     * Display single flight details.
     */
    public function show(Flight $flight)
    {
        // Check if flight has departed
        if ($flight->isPast()) {
            return redirect()->route('flights.search')->with('error', 'This flight has already departed.');
        }

        $flight->load('aircraft', 'seats.fareClass');
        $fareClasses = FareClass::with('fareRule')->get();

        // Track search activity for demand calculation (Phase 5 Task 2 & 7)
        // Log the search
        FlightSearch::logSearch(
            $flight->id,
            auth()->id(),
            [
                'origin' => $flight->origin,
                'destination' => $flight->destination,
            ],
            request()->ip()
        );
        
        // Increase demand score
        $flight->increaseSearchDemand();

        $prices = [];
        $trends = [];
        $availability = [];
        $fareRules = [];

        foreach ($fareClasses as $fareClass) {
            $prices[$fareClass->id] = $this->pricingService->calculateCurrentPrice($flight, $fareClass);
            $trends[$fareClass->id] = $this->pricingService->getPriceTrend($flight, $fareClass);
            $availability[$fareClass->id] = $flight->availableSeatsForFareClass($fareClass->id);
            $fareRules[$fareClass->id] = $this->fareRuleService->getRuleSummary($fareClass);
        }

        return view('flights.show', compact(
            'flight',
            'fareClasses',
            'prices',
            'trends',
            'availability',
            'fareRules'
        ));
    }
}
