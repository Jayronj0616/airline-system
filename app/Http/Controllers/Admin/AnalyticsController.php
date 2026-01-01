<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Flight;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        $analytics = [
            'booking_heatmap' => $this->getBookingHeatmap(),
            'revenue_forecast' => $this->getRevenueForecast(),
            'top_routes' => $this->getTopPerformingRoutes(),
            'occupancy_trends' => $this->getOccupancyTrends(),
            'booking_window_analysis' => $this->getBookingWindowAnalysis(),
        ];

        return view('admin.analytics.index', compact('analytics'));
    }

    /**
     * Get booking heatmap - which hours get most bookings
     */
    private function getBookingHeatmap()
    {
        $heatmapData = Booking::where('status', 'confirmed')
            ->where('confirmed_at', '>=', Carbon::now()->subDays(30))
            ->select(
                DB::raw('DAYOFWEEK(confirmed_at) as day_of_week'),
                DB::raw('HOUR(confirmed_at) as hour'),
                DB::raw('COUNT(*) as booking_count')
            )
            ->groupBy('day_of_week', 'hour')
            ->get();

        // Create 7x24 matrix (days x hours)
        $matrix = array_fill(1, 7, array_fill(0, 24, 0));

        foreach ($heatmapData as $data) {
            $matrix[$data->day_of_week][$data->hour] = $data->booking_count;
        }

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $hours = range(0, 23);

        return [
            'matrix' => $matrix,
            'days' => $days,
            'hours' => $hours,
            'max_bookings' => max(array_map('max', $matrix)),
        ];
    }

    /**
     * Get revenue forecast for next 30 days
     */
    private function getRevenueForecast()
    {
        // Historical revenue (last 30 days)
        $historical = [];
        for ($i = 30; $i > 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Booking::where('status', 'confirmed')
                ->whereDate('confirmed_at', $date)
                ->sum('total_price') ?? 0;
            
            $historical[] = [
                'date' => $date->format('Y-m-d'),
                'revenue' => round($revenue, 2),
            ];
        }

        // Calculate trend (simple moving average)
        $recentRevenues = array_slice(array_column($historical, 'revenue'), -7);
        $avgDailyRevenue = array_sum($recentRevenues) / count($recentRevenues);
        
        // Forecast next 30 days with 5% growth trend
        $forecast = [];
        for ($i = 1; $i <= 30; $i++) {
            $date = Carbon::now()->addDays($i);
            $projectedRevenue = $avgDailyRevenue * (1 + (0.05 * ($i / 30)));
            
            $forecast[] = [
                'date' => $date->format('Y-m-d'),
                'revenue' => round($projectedRevenue, 2),
            ];
        }

        return [
            'historical' => $historical,
            'forecast' => $forecast,
            'avg_daily' => round($avgDailyRevenue, 2),
            'projected_monthly' => round($avgDailyRevenue * 30 * 1.05, 2),
        ];
    }

    /**
     * Get top performing routes
     */
    private function getTopPerformingRoutes()
    {
        $routes = DB::table('bookings')
            ->join('flights', 'bookings.flight_id', '=', 'flights.id')
            ->where('bookings.status', 'confirmed')
            ->where('bookings.confirmed_at', '>=', Carbon::now()->subDays(30))
            ->select(
                DB::raw('CONCAT(flights.origin, " → ", flights.destination) as route'),
                DB::raw('COUNT(bookings.id) as total_bookings'),
                DB::raw('SUM(bookings.total_price) as total_revenue'),
                DB::raw('AVG(bookings.total_price) as avg_price'),
                DB::raw('SUM(bookings.seat_count) as total_passengers')
            )
            ->groupBy('flights.origin', 'flights.destination')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        return [
            'routes' => $routes,
            'labels' => $routes->pluck('route')->toArray(),
            'revenues' => $routes->pluck('total_revenue')->map(fn($r) => round($r, 2))->toArray(),
            'bookings' => $routes->pluck('total_bookings')->toArray(),
            'passengers' => $routes->pluck('total_passengers')->toArray(),
        ];
    }

    /**
     * Get occupancy rate trends over time
     */
    private function getOccupancyTrends()
    {
        $trends = [];
        
        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            $flights = Flight::whereDate('departure_time', $date)
                ->with(['aircraft', 'bookings' => function($q) {
                    $q->where('status', 'confirmed');
                }])
                ->get();
            
            if ($flights->isEmpty()) {
                continue;
            }
            
            $totalSeats = $flights->sum(fn($f) => $f->aircraft->total_seats);
            $bookedSeats = $flights->sum(fn($f) => $f->bookings->sum('seat_count'));
            
            $occupancyRate = $totalSeats > 0 ? ($bookedSeats / $totalSeats) * 100 : 0;
            
            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'occupancy_rate' => round($occupancyRate, 2),
                'total_flights' => $flights->count(),
            ];
        }

        return [
            'trends' => $trends,
            'labels' => array_column($trends, 'date'),
            'occupancy_rates' => array_column($trends, 'occupancy_rate'),
            'avg_occupancy' => round(array_sum(array_column($trends, 'occupancy_rate')) / count($trends), 2),
        ];
    }

    /**
     * Get booking window analysis (how far in advance people book)
     */
    private function getBookingWindowAnalysis()
    {
        $bookings = Booking::where('status', 'confirmed')
            ->where('confirmed_at', '>=', Carbon::now()->subDays(30))
            ->with('flight:id,departure_time')
            ->get(['id', 'flight_id', 'confirmed_at']);

        $windows = [
            '0-7 days' => 0,
            '8-14 days' => 0,
            '15-21 days' => 0,
            '22-30 days' => 0,
            '31+ days' => 0,
        ];

        foreach ($bookings as $booking) {
            $daysInAdvance = $booking->confirmed_at->diffInDays($booking->flight->departure_time);
            
            if ($daysInAdvance <= 7) {
                $windows['0-7 days']++;
            } elseif ($daysInAdvance <= 14) {
                $windows['8-14 days']++;
            } elseif ($daysInAdvance <= 21) {
                $windows['15-21 days']++;
            } elseif ($daysInAdvance <= 30) {
                $windows['22-30 days']++;
            } else {
                $windows['31+ days']++;
            }
        }

        $total = array_sum($windows);
        $percentages = [];
        foreach ($windows as $key => $count) {
            $percentages[$key] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        return [
            'windows' => $windows,
            'percentages' => $percentages,
            'labels' => array_keys($windows),
            'data' => array_values($windows),
            'total_bookings' => $total,
            'avg_days_advance' => $this->calculateAverageDaysAdvance($bookings),
        ];
    }

    private function calculateAverageDaysAdvance($bookings)
    {
        if ($bookings->isEmpty()) {
            return 0;
        }

        $totalDays = $bookings->sum(function($booking) {
            return $booking->confirmed_at->diffInDays($booking->flight->departure_time);
        });

        return round($totalDays / $bookings->count(), 1);
    }
}
