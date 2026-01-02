<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\PriceHistory;
use App\Models\FareClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'flight_id' => $request->input('flight_id'),
            'fare_class_id' => $request->input('fare_class_id'),
            'page' => $request->input('page', 1),
        ];

        $metrics = Cache::remember('admin_dashboard_metrics', 300, function () {
            return $this->calculateMetrics();
        });

        $chartData = Cache::remember('admin_dashboard_charts', 300, function () {
            return $this->getChartData();
        });

        $flightPerformance = $this->getFlightPerformanceData($filters);
        $demandTrends = $this->getDemandTrends();

        $allFlights = Flight::where('departure_time', '>', now())
            ->orderBy('flight_number')
            ->get(['id', 'flight_number', 'origin', 'destination']);

        $fareClasses = FareClass::all(['id', 'name']);

        $priceHistoryData = null;
        if ($filters['flight_id']) {
            $priceHistoryData = $this->getPriceHistoryData($filters['flight_id']);
        }

        return view('admin.dashboard.index', compact(
            'metrics',
            'chartData',
            'flightPerformance',
            'demandTrends',
            'allFlights',
            'fareClasses',
            'filters',
            'priceHistoryData'
        ));
    }

    private function calculateMetrics()
    {
        return [
            'total_revenue' => $this->getTotalRevenue(),
            'total_bookings' => $this->getTotalBookings(),
            'average_load_factor' => $this->getAverageLoadFactor(),
            'average_ticket_price' => $this->getAverageTicketPrice(),
        ];
    }

    private function getTotalRevenue()
    {
        return Booking::where('status', 'confirmed')
            ->whereMonth('confirmed_at', now()->month)
            ->whereYear('confirmed_at', now()->year)
            ->sum('total_price') ?? 0;
    }

    private function getTotalBookings()
    {
        return Booking::where('status', 'confirmed')
            ->whereMonth('confirmed_at', now()->month)
            ->whereYear('confirmed_at', now()->year)
            ->count();
    }

    private function getAverageLoadFactor()
    {
        $flights = Flight::with(['aircraft', 'bookings' => function ($query) {
            $query->where('status', 'confirmed')->select('flight_id', 'seat_count');
        }])->get();

        if ($flights->isEmpty()) {
            return 0;
        }

        $totalLoadFactor = 0;
        $flightCount = 0;

        foreach ($flights as $flight) {
            if (!$flight->aircraft) continue;
            
            $totalSeats = $flight->aircraft->total_seats;
            if ($totalSeats > 0) {
                $confirmedBookings = $flight->bookings->sum('seat_count');
                $loadFactor = ($confirmedBookings / $totalSeats) * 100;
                $totalLoadFactor += $loadFactor;
                $flightCount++;
            }
        }

        return $flightCount > 0 ? round($totalLoadFactor / $flightCount, 2) : 0;
    }

    private function getAverageTicketPrice()
    {
        $average = Booking::where('status', 'confirmed')
            ->whereMonth('confirmed_at', now()->month)
            ->whereYear('confirmed_at', now()->year)
            ->avg('total_price');

        return $average ? round($average, 2) : 0;
    }

    private function getChartData()
    {
        return [
            'revenue_over_time' => $this->getRevenueOverTime(),
            'bookings_by_fare_class' => $this->getBookingsByFareClass(),
            'load_factor_by_flight' => $this->getLoadFactorByFlight(),
            'price_vs_demand' => $this->getPriceVsDemand(),
        ];
    }

    private function getRevenueOverTime()
    {
        $days = [];
        $revenues = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('M d');
            
            $revenue = Booking::where('status', 'confirmed')
                ->whereDate('confirmed_at', $date->format('Y-m-d'))
                ->sum('total_price') ?? 0;
            
            $revenues[] = round($revenue, 2);
        }

        return [
            'labels' => $days,
            'data' => $revenues,
        ];
    }

    private function getBookingsByFareClass()
    {
        $bookings = Booking::where('status', 'confirmed')
            ->with('fareClass:id,name')
            ->select('fare_class_id', DB::raw('count(*) as count'))
            ->groupBy('fare_class_id')
            ->get();

        return [
            'labels' => $bookings->pluck('fareClass.name')->toArray(),
            'data' => $bookings->pluck('count')->toArray(),
        ];
    }

    private function getLoadFactorByFlight()
    {
        $flights = Flight::with(['aircraft:id,total_seats', 'bookings' => function ($query) {
            $query->where('status', 'confirmed')->select('flight_id', 'seat_count');
        }])
        ->where('departure_time', '>', now())
        ->orderBy('departure_time', 'asc')
        ->limit(15)
        ->get(['id', 'flight_number', 'aircraft_id']);

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($flights as $flight) {
            if (!$flight->aircraft) continue;
            
            $totalSeats = $flight->aircraft->total_seats;
            if ($totalSeats > 0) {
                $confirmedSeats = $flight->bookings->sum('seat_count');
                $loadFactor = round(($confirmedSeats / $totalSeats) * 100, 2);
                
                $labels[] = $flight->flight_number;
                $data[] = $loadFactor;
                
                if ($loadFactor >= 85) {
                    $colors[] = 'rgb(34, 197, 94)';
                } elseif ($loadFactor >= 70) {
                    $colors[] = 'rgb(234, 179, 8)';
                } else {
                    $colors[] = 'rgb(239, 68, 68)';
                }
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
        ];
    }

    private function getPriceVsDemand()
    {
        $flights = Flight::where('departure_time', '>', now())
            ->get();

        $data = [];

        foreach ($flights as $flight) {
            $avgPrice = ($flight->base_price_economy + $flight->base_price_business + $flight->base_price_first) / 3;
            
            $data[] = [
                'x' => round($flight->demand_score, 2),
                'y' => round($avgPrice, 2),
                'label' => $flight->flight_number,
            ];
        }

        return $data;
    }

    private function getFlightPerformanceData($filters = [])
    {
        $query = Flight::with(['aircraft:id,total_seats', 'bookings' => function ($query) use ($filters) {
            $query->where('status', 'confirmed')
                  ->select('flight_id', 'seat_count', 'total_price', 'confirmed_at', 'fare_class_id');
            
            if (!empty($filters['date_from'])) {
                $query->whereDate('confirmed_at', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->whereDate('confirmed_at', '<=', $filters['date_to']);
            }
            
            if (!empty($filters['fare_class_id'])) {
                $query->where('fare_class_id', $filters['fare_class_id']);
            }
        }])
        ->select('id', 'flight_number', 'origin', 'destination', 'departure_time', 'aircraft_id')
        ->where('departure_time', '>', now());

        if (!empty($filters['flight_id'])) {
            $query->where('id', $filters['flight_id']);
        }

        $flights = $query->orderBy('departure_time', 'asc')->get();

        $performanceData = [];

        foreach ($flights as $flight) {
            if (!$flight->aircraft) continue;
            
            $totalSeats = $flight->aircraft->total_seats;
            $confirmedSeats = $flight->bookings->sum('seat_count');
            $revenue = $flight->bookings->sum('total_price');
            $bookingCount = $flight->bookings->count();
            
            $loadFactor = $totalSeats > 0 ? round(($confirmedSeats / $totalSeats) * 100, 2) : 0;
            $avgTicketPrice = $bookingCount > 0 ? round($revenue / $bookingCount, 2) : 0;

            $performanceData[] = [
                'flight_number' => $flight->flight_number,
                'route' => $flight->origin . ' → ' . $flight->destination,
                'departure_time' => $flight->departure_time,
                'load_factor' => $loadFactor,
                'revenue' => $revenue,
                'avg_ticket_price' => $avgTicketPrice,
                'seats_sold' => $confirmedSeats,
                'total_seats' => $totalSeats,
            ];
        }

        // Paginate
        $page = $filters['page'] ?? 1;
        $perPage = 10;
        $total = count($performanceData);
        $items = array_slice($performanceData, ($page - 1) * $perPage, $perPage);

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    private function getDemandTrends()
    {
        $highDemand = Flight::where('departure_time', '>', now())
            ->orderBy('demand_score', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($flight) {
                return [
                    'flight_number' => $flight->flight_number,
                    'route' => $flight->origin . ' → ' . $flight->destination,
                    'demand_score' => round($flight->demand_score, 2),
                    'load_factor' => $flight->load_factor,
                    'avg_price' => round(($flight->base_price_economy + $flight->base_price_business + $flight->base_price_first) / 3, 2),
                    'suggestion' => $this->getPriceSuggestion($flight),
                ];
            });

        $lowDemand = Flight::where('departure_time', '>', now())
            ->orderBy('demand_score', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($flight) {
                return [
                    'flight_number' => $flight->flight_number,
                    'route' => $flight->origin . ' → ' . $flight->destination,
                    'demand_score' => round($flight->demand_score, 2),
                    'load_factor' => $flight->load_factor,
                    'avg_price' => round(($flight->base_price_economy + $flight->base_price_business + $flight->base_price_first) / 3, 2),
                    'suggestion' => $this->getPriceSuggestion($flight),
                ];
            });

        return [
            'high_demand' => $highDemand,
            'low_demand' => $lowDemand,
        ];
    }

    private function getPriceSuggestion($flight)
    {
        $demandScore = $flight->demand_score;
        $loadFactor = $flight->load_factor;

        if ($demandScore >= 70 && $loadFactor >= 80) {
            return [
                'action' => 'increase',
                'percentage' => 15,
                'reason' => 'High demand and high load factor',
                'color' => 'green',
            ];
        }

        if ($demandScore >= 70 && $loadFactor >= 50) {
            return [
                'action' => 'increase',
                'percentage' => 10,
                'reason' => 'High demand, room to fill',
                'color' => 'green',
            ];
        }

        if ($demandScore < 40 && $loadFactor < 50) {
            return [
                'action' => 'decrease',
                'percentage' => 20,
                'reason' => 'Low demand and low load factor',
                'color' => 'red',
            ];
        }

        if ($demandScore < 40 && $loadFactor < 70) {
            return [
                'action' => 'decrease',
                'percentage' => 10,
                'reason' => 'Low demand, needs boost',
                'color' => 'orange',
            ];
        }

        return [
            'action' => 'hold',
            'percentage' => 0,
            'reason' => 'Balanced demand and load factor',
            'color' => 'blue',
        ];
    }

    public function exportCSV()
    {
        $performanceData = $this->getFlightPerformanceData();

        $filename = 'flight_performance_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($performanceData) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['Flight', 'Route', 'Departure Time', 'Load Factor (%)', 'Revenue (₱)', 'Avg Ticket Price (₱)', 'Seats Sold', 'Total Seats']);
            
            foreach ($performanceData as $row) {
                fputcsv($file, [
                    $row['flight_number'],
                    $row['route'],
                    $row['departure_time']->format('Y-m-d H:i'),
                    $row['load_factor'],
                    $row['revenue'],
                    $row['avg_ticket_price'],
                    $row['seats_sold'],
                    $row['total_seats'],
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getPriceHistoryData($flightId)
    {
        $flight = Flight::select('id', 'flight_number', 'origin', 'destination')->find($flightId);
        if (!$flight) {
            return null;
        }

        $priceHistory = PriceHistory::where('flight_id', $flightId)
            ->with('fareClass:id,name')
            ->select('flight_id', 'fare_class_id', 'price', 'recorded_at')
            ->orderBy('recorded_at', 'asc')
            ->get();

        if ($priceHistory->isEmpty()) {
            return null;
        }

        $bookings = Booking::where('flight_id', $flightId)
            ->where('status', 'confirmed')
            ->select('confirmed_at', 'total_price', 'seat_count')
            ->orderBy('confirmed_at', 'asc')
            ->get();

        $pricesByFareClass = $priceHistory->groupBy('fare_class_id');

        $datasets = [];
        $colors = [
            'rgb(59, 130, 246)',
            'rgb(168, 85, 247)',
            'rgb(234, 179, 8)',
        ];
        $colorIndex = 0;

        foreach ($pricesByFareClass as $fareClassId => $prices) {
            $fareClassName = $prices->first()->fareClass->name ?? 'Unknown';
            
            $datasets[] = [
                'label' => $fareClassName,
                'data' => $prices->map(fn($p) => [
                    'x' => $p->recorded_at->format('Y-m-d H:i'),
                    'y' => (float) $p->price,
                ])->toArray(),
                'borderColor' => $colors[$colorIndex % count($colors)],
                'backgroundColor' => 'transparent',
                'tension' => 0.4,
            ];
            
            $colorIndex++;
        }

        $bookingEvents = $bookings->map(fn($b) => [
            'x' => $b->confirmed_at->format('Y-m-d H:i'),
            'seats' => $b->seat_count,
            'price' => (float) $b->total_price,
        ])->toArray();

        return [
            'flight' => $flight,
            'datasets' => $datasets,
            'bookingEvents' => $bookingEvents,
        ];
    }
}
