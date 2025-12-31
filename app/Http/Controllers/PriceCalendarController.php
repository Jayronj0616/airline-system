<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\FareClass;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PriceCalendarController extends Controller
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Show price calendar for a route.
     */
    public function show(Request $request)
    {
        $request->validate([
            'origin' => 'required|string|size:3',
            'destination' => 'required|string|size:3',
            'departure_date' => 'nullable|date',
        ]);

        $origin = strtoupper($request->origin);
        $destination = strtoupper($request->destination);
        $baseDate = $request->departure_date ? Carbon::parse($request->departure_date) : Carbon::today();

        // Get 7 days starting from base date
        $dates = collect();
        for ($i = 0; $i < 7; $i++) {
            $date = $baseDate->copy()->addDays($i);
            $dates->push($date);
        }

        // Get flights for each date
        $calendar = [];
        foreach ($dates as $date) {
            $flights = Flight::where('origin', $origin)
                ->where('destination', $destination)
                ->whereDate('departure_time', $date)
                ->with(['aircraft', 'fareClasses'])
                ->get();

            $dayPrices = [];
            foreach ($flights as $flight) {
                foreach ($flight->fareClasses as $fareClass) {
                    $price = $this->pricingService->calculateCurrentPrice($flight, $fareClass);
                    $available = $flight->seats()
                        ->where('fare_class_id', $fareClass->id)
                        ->where('status', 'available')
                        ->count();

                    if (!isset($dayPrices[$fareClass->name]) || $price < $dayPrices[$fareClass->name]['price']) {
                        $dayPrices[$fareClass->name] = [
                            'price' => $price,
                            'flight' => $flight,
                            'fareClass' => $fareClass,
                            'available' => $available,
                        ];
                    }
                }
            }

            $calendar[] = [
                'date' => $date,
                'prices' => $dayPrices,
            ];
        }

        return view('price-calendar.show', compact('calendar', 'origin', 'destination', 'baseDate'));
    }
}
