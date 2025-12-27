<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use App\Services\OverbookingService;
use Illuminate\Http\Request;

class OverbookingController extends Controller
{
    protected $overbookingService;

    public function __construct(OverbookingService $overbookingService)
    {
        $this->overbookingService = $overbookingService;
    }

    /**
     * Display overbooking management index page.
     */
    public function index()
    {
        $flights = Flight::with('aircraft')
            ->where('departure_time', '>', now())
            ->orderBy('departure_time')
            ->paginate(20);

        // Calculate stats for each flight
        $flightsWithStats = $flights->through(function ($flight) {
            $flight->overbooking_stats = $this->overbookingService->getOverbookingStats($flight);
            $flight->risk_assessment = $this->overbookingService->calculateDeniedBoardingRisk($flight);
            return $flight;
        });

        // Count flights at risk
        $atRiskCount = Flight::where('departure_time', '>', now())
            ->get()
            ->filter(function ($flight) {
                return $this->overbookingService->isAtRiskOfDeniedBoarding($flight);
            })
            ->count();

        return view('admin.overbooking.index', compact('flights', 'atRiskCount'));
    }

    /**
     * Display detailed overbooking page for a specific flight.
     */
    public function edit(Flight $flight)
    {
        $stats = $this->overbookingService->getOverbookingStats($flight);
        $riskAssessment = $this->overbookingService->calculateDeniedBoardingRisk($flight);
        $recommendedPercentage = $this->overbookingService->calculateRecommendedOverbooking($flight);
        $expectedNoShows = $this->overbookingService->calculateExpectedNoShows($flight);

        // Get denied boarding history if any
        $deniedBoardings = $flight->deniedBoardings()->with('booking.user')->latest()->get();

        return view('admin.overbooking.edit', compact(
            'flight',
            'stats',
            'riskAssessment',
            'recommendedPercentage',
            'expectedNoShows',
            'deniedBoardings'
        ));
    }

    /**
     * Toggle overbooking for a specific flight.
     */
    public function toggle(Request $request, Flight $flight)
    {
        $enabled = $request->input('enabled');

        if ($enabled) {
            // Get percentage from request or use default
            $percentage = $request->input('percentage', 10.0);
            
            $success = $this->overbookingService->enableOverbooking($flight, $percentage);
            
            if (!$success) {
                return redirect()
                    ->back()
                    ->with('error', 'Cannot enable overbooking. Flight must be more than 7 days away and percentage must be between 0-15%.');
            }
            
            return redirect()
                ->back()
                ->with('success', 'Overbooking enabled successfully.');
        } else {
            $this->overbookingService->disableOverbooking($flight);
            
            return redirect()
                ->back()
                ->with('success', 'Overbooking disabled successfully.');
        }
    }

    /**
     * Update overbooking percentage for a flight.
     */
    public function updatePercentage(Request $request, Flight $flight)
    {
        $validated = $request->validate([
            'overbooking_percentage' => 'required|numeric|min:0|max:15',
        ]);

        if (!$this->overbookingService->canOverbook($flight) && $flight->overbooking_enabled) {
            return redirect()
                ->back()
                ->with('error', 'Cannot update overbooking percentage. Flight is too close to departure.');
        }

        $flight->update([
            'overbooking_percentage' => $validated['overbooking_percentage'],
        ]);

        return redirect()
            ->back()
            ->with('success', 'Overbooking percentage updated successfully.');
    }

    /**
     * Enable overbooking globally for all eligible flights.
     */
    public function enableGlobal(Request $request)
    {
        $validated = $request->validate([
            'percentage' => 'required|numeric|min:0|max:15',
        ]);

        $percentage = $validated['percentage'];
        $count = 0;

        $flights = Flight::where('departure_time', '>', now())
            ->get();

        foreach ($flights as $flight) {
            if ($this->overbookingService->enableOverbooking($flight, $percentage)) {
                $count++;
            }
        }

        return redirect()
            ->back()
            ->with('success', "Overbooking enabled for {$count} eligible flights at {$percentage}%.");
    }

    /**
     * Disable overbooking globally for all flights.
     */
    public function disableGlobal()
    {
        $flights = Flight::where('overbooking_enabled', true)
            ->where('departure_time', '>', now())
            ->get();

        $count = 0;
        foreach ($flights as $flight) {
            $this->overbookingService->disableOverbooking($flight);
            $count++;
        }

        return redirect()
            ->back()
            ->with('success', "Overbooking disabled for {$count} flights.");
    }

    /**
     * View flights at risk of denied boarding.
     */
    public function atRisk()
    {
        $flights = Flight::with('aircraft')
            ->where('departure_time', '>', now())
            ->get()
            ->filter(function ($flight) {
                return $this->overbookingService->isAtRiskOfDeniedBoarding($flight);
            });

        // Calculate stats for each flight
        $flightsWithStats = $flights->map(function ($flight) {
            $flight->overbooking_stats = $this->overbookingService->getOverbookingStats($flight);
            $flight->risk_assessment = $this->overbookingService->calculateDeniedBoardingRisk($flight);
            return $flight;
        });

        return view('admin.overbooking.at-risk', compact('flightsWithStats'));
    }

    /**
     * Recalculate overbooked count for all flights.
     */
    public function recalculateAll()
    {
        $flights = Flight::where('departure_time', '>', now())->get();
        $count = 0;

        foreach ($flights as $flight) {
            $this->overbookingService->updateOverbookedCount($flight);
            $count++;
        }

        return redirect()
            ->back()
            ->with('success', "Recalculated overbooked count for {$count} flights.");
    }
}
