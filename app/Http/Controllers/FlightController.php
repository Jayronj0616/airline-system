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
        $query = Flight::with('aircraft')
            ->where('departure_time', '>', Carbon::now());

        // Filter by origin
        if ($request->filled('origin')) {
            $query->where('origin', 'LIKE', "%{$request->origin}%");
        }

        // Filter by destination
        if ($request->filled('destination')) {
            $query->where('destination', 'LIKE', "%{$request->destination}%");
        }

        // Filter by date
        if ($request->filled('date')) {
            $date = Carbon::parse($request->date);
            $query->whereDate('departure_time', $date);
        }
        
        // Filter by time of day
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
        
        // Sort
        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'price_asc':
                case 'price_desc':
                    // Will sort after price calculation
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
        
        $flights = $query->paginate(10);
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
        
        // Filter by price range
        if ($request->filled('price_min') || $request->filled('price_max')) {
            $flights = $flights->filter(function($flight) use ($request, $fareClasses, $flightPrices) {
                $minPrice = null;
                foreach ($fareClasses as $fareClass) {
                    $key = "{$flight->id}_{$fareClass->id}";
                    $price = $flightPrices[$key];
                    if ($price && ($minPrice === null || $price < $minPrice)) {
                        $minPrice = $price;
                    }
                }
                
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
            $flights = $flights->sort(function($a, $b) use ($fareClasses, $flightPrices, $request) {
                $minPriceA = null;
                $minPriceB = null;
                
                foreach ($fareClasses as $fareClass) {
                    $keyA = "{$a->id}_{$fareClass->id}";
                    $keyB = "{$b->id}_{$fareClass->id}";
                    $priceA = $flightPrices[$keyA];
                    $priceB = $flightPrices[$keyB];
                    
                    if ($priceA && ($minPriceA === null || $priceA < $minPriceA)) {
                        $minPriceA = $priceA;
                    }
                    if ($priceB && ($minPriceB === null || $priceB < $minPriceB)) {
                        $minPriceB = $priceB;
                    }
                }
                
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
            'fareRules'
        ));
    }

    /**
     * Display single flight details.
     */
    public function show(Flight $flight)
    {
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
