<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use App\Services\PricingService;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Display pricing management page for a flight.
     */
    public function edit(Flight $flight)
    {
        return view('admin.pricing.edit', compact('flight'));
    }

    /**
     * Update base fares for a flight.
     */
    public function update(Request $request, Flight $flight)
    {
        $validated = $request->validate([
            'base_price_economy' => 'required|numeric|min:0',
            'base_price_business' => 'required|numeric|min:0',
            'base_price_first' => 'required|numeric|min:0',
        ]);

        $flight->update($validated);

        // Recalculate and record new prices
        $this->pricingService->updateFlightPrices($flight);

        return redirect()
            ->route('admin.pricing.edit', $flight)
            ->with('success', 'Base fares updated successfully. Prices have been recalculated.');
    }

    /**
     * Display all flights with pricing overview.
     */
    public function index()
    {
        $flights = Flight::with('aircraft')
            ->where('departure_time', '>', now())
            ->orderBy('departure_time')
            ->paginate(20);

        return view('admin.pricing.index', compact('flights'));
    }

    /**
     * Manually trigger price recalculation for a flight.
     */
    public function recalculate(Flight $flight)
    {
        $prices = $this->pricingService->updateFlightPrices($flight);

        return redirect()
            ->back()
            ->with('success', 'Prices recalculated and recorded to history.');
    }

    /**
     * Bulk recalculate all future flights.
     */
    public function recalculateAll()
    {
        $flights = Flight::where('departure_time', '>', now())->get();
        $count = 0;

        foreach ($flights as $flight) {
            $this->pricingService->updateFlightPrices($flight);
            $count++;
        }

        return redirect()
            ->back()
            ->with('success', "Recalculated prices for {$count} flights.");
    }
}
