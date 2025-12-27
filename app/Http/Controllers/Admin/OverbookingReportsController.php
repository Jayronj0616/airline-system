<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use App\Models\Booking;
use App\Models\DeniedBoarding;
use App\Services\OverbookingService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OverbookingReportsController extends Controller
{
    protected $overbookingService;

    public function __construct(OverbookingService $overbookingService)
    {
        $this->overbookingService = $overbookingService;
    }

    /**
     * Display overbooking reports and analytics.
     */
    public function index(Request $request)
    {
        // Date range filter (default: last 30 days)
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Get flights in date range
        $flights = Flight::with('aircraft')
            ->whereBetween('departure_time', [$startDate, $endDate])
            ->get();

        // Calculate aggregate statistics
        $stats = $this->calculateAggregateStats($flights);

        // Get top performing flights (highest load factor with overbooking)
        $topPerformers = $this->getTopPerformingFlights($flights, 10);

        // Get denied boarding statistics
        $deniedBoardingStats = $this->getDeniedBoardingStats($startDate, $endDate);

        // Calculate revenue impact
        $revenueImpact = $this->calculateRevenueImpact($flights, $deniedBoardingStats);

        return view('admin.overbooking.reports', compact(
            'stats',
            'topPerformers',
            'deniedBoardingStats',
            'revenueImpact',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Calculate aggregate overbooking statistics.
     */
    protected function calculateAggregateStats($flights)
    {
        $totalFlights = $flights->count();
        $overbookedFlights = 0;
        $totalPhysicalCapacity = 0;
        $totalConfirmedBookings = 0;
        $highLoadFactorCount = 0;

        foreach ($flights as $flight) {
            $stats = $this->overbookingService->getOverbookingStats($flight);
            
            if ($stats['overbooked_count'] > 0) {
                $overbookedFlights++;
            }

            $totalPhysicalCapacity += $stats['physical_capacity'];
            $totalConfirmedBookings += $stats['confirmed_bookings'];

            if ($stats['load_factor'] >= 90) {
                $highLoadFactorCount++;
            }
        }

        $avgLoadFactor = $totalPhysicalCapacity > 0 
            ? round(($totalConfirmedBookings / $totalPhysicalCapacity) * 100, 2)
            : 0;

        return [
            'total_flights' => $totalFlights,
            'overbooked_flights' => $overbookedFlights,
            'overbooked_percentage' => $totalFlights > 0 ? round(($overbookedFlights / $totalFlights) * 100, 2) : 0,
            'total_physical_capacity' => $totalPhysicalCapacity,
            'total_confirmed_bookings' => $totalConfirmedBookings,
            'average_load_factor' => $avgLoadFactor,
            'high_load_factor_count' => $highLoadFactorCount,
            'high_load_factor_percentage' => $totalFlights > 0 ? round(($highLoadFactorCount / $totalFlights) * 100, 2) : 0,
        ];
    }

    /**
     * Get top performing flights by load factor.
     */
    protected function getTopPerformingFlights($flights, $limit = 10)
    {
        return $flights->map(function ($flight) {
            $stats = $this->overbookingService->getOverbookingStats($flight);
            $flight->overbooking_stats = $stats;
            return $flight;
        })
        ->sortByDesc('overbooking_stats.load_factor')
        ->take($limit);
    }

    /**
     * Get denied boarding statistics.
     */
    protected function getDeniedBoardingStats($startDate, $endDate)
    {
        $deniedBoardings = DeniedBoarding::whereBetween('denied_at', [$startDate, $endDate])->get();

        $totalDenied = $deniedBoardings->count();
        $totalCompensation = $deniedBoardings->sum('compensation_amount');

        $byFareClass = $deniedBoardings->groupBy('fare_class_id')->map(function ($group) {
            return $group->count();
        });

        $byResolution = $deniedBoardings->groupBy('resolution_type')->map(function ($group) {
            return $group->count();
        });

        return [
            'total_denied' => $totalDenied,
            'total_compensation' => $totalCompensation,
            'by_fare_class' => $byFareClass,
            'by_resolution' => $byResolution,
            'avg_compensation' => $totalDenied > 0 ? round($totalCompensation / $totalDenied, 2) : 0,
        ];
    }

    /**
     * Calculate revenue impact of overbooking.
     */
    protected function calculateRevenueImpact($flights, $deniedBoardingStats)
    {
        $revenueGained = 0;
        $additionalBookings = 0;

        foreach ($flights as $flight) {
            $stats = $this->overbookingService->getOverbookingStats($flight);
            
            // If overbooked, estimate additional revenue
            if ($stats['overbooked_count'] > 0) {
                // Get average ticket price for this flight
                $avgPrice = Booking::where('flight_id', $flight->id)
                    ->whereIn('status', ['confirmed', 'checked_in'])
                    ->avg('total_price');

                if ($avgPrice) {
                    $revenueGained += $avgPrice * $stats['overbooked_count'];
                    $additionalBookings += $stats['overbooked_count'];
                }
            }
        }

        $revenueLost = $deniedBoardingStats['total_compensation'];
        $netRevenue = $revenueGained - $revenueLost;

        return [
            'revenue_gained' => round($revenueGained, 2),
            'additional_bookings' => $additionalBookings,
            'compensation_paid' => $revenueLost,
            'net_revenue' => round($netRevenue, 2),
            'roi' => $revenueGained > 0 ? round(($netRevenue / $revenueGained) * 100, 2) : 0,
        ];
    }

    /**
     * Export overbooking report as CSV.
     */
    public function export(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $flights = Flight::with('aircraft')
            ->whereBetween('departure_time', [$startDate, $endDate])
            ->get();

        $filename = "overbooking_report_{$startDate}_to_{$endDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($flights) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Flight Number',
                'Origin',
                'Destination',
                'Departure Date',
                'Physical Capacity',
                'Virtual Capacity',
                'Confirmed Bookings',
                'Overbooked Count',
                'Load Factor (%)',
                'Overbooking Enabled',
                'Overbooking Percentage',
                'Risk Level',
            ]);

            foreach ($flights as $flight) {
                $stats = $this->overbookingService->getOverbookingStats($flight);
                $risk = $this->overbookingService->calculateDeniedBoardingRisk($flight);

                fputcsv($file, [
                    $flight->flight_number,
                    $flight->origin,
                    $flight->destination,
                    $flight->departure_time->format('Y-m-d H:i'),
                    $stats['physical_capacity'],
                    $stats['virtual_capacity'],
                    $stats['confirmed_bookings'],
                    $stats['overbooked_count'],
                    $stats['load_factor'],
                    $stats['overbooking_enabled'] ? 'Yes' : 'No',
                    $stats['overbooking_percentage'],
                    ucfirst($risk['risk_level']),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
