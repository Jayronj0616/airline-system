<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingAddOn;
use App\Models\Flight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $metrics = [
            'total_bookings' => $this->getTotalBookings($dateFrom, $dateTo),
            'total_revenue' => $this->getTotalRevenue($dateFrom, $dateTo),
            'total_revenue_unpaid' => $this->getTotalRevenueUnpaid($dateFrom, $dateTo),
            'cancelled_bookings' => $this->getCancelledBookings($dateFrom, $dateTo),
            'avg_load_factor' => $this->getAverageLoadFactor($dateFrom, $dateTo),
            'addon_revenue' => $this->getAddonRevenue($dateFrom, $dateTo),
        ];

        $bookingsPerFlight = $this->getBookingsPerFlight($dateFrom, $dateTo);
        $revenueByFareClass = $this->getRevenueByFareClass($dateFrom, $dateTo);
        $topAddons = $this->getTopAddons($dateFrom, $dateTo);

        return view('admin.reports.index', compact(
            'metrics',
            'bookingsPerFlight',
            'revenueByFareClass',
            'topAddons',
            'dateFrom',
            'dateTo'
        ));
    }

    private function getTotalBookings($dateFrom, $dateTo)
    {
        return Booking::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('status', ['confirmed', 'confirmed_paid', 'held'])
            ->count();
    }

    private function getTotalRevenue($dateFrom, $dateTo)
    {
        return Booking::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'confirmed_paid')
            ->sum('total_price') ?? 0;
    }

    private function getTotalRevenueUnpaid($dateFrom, $dateTo)
    {
        return Booking::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('status', ['confirmed', 'held'])
            ->sum('total_price') ?? 0;
    }

    private function getCancelledBookings($dateFrom, $dateTo)
    {
        return Booking::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'cancelled')
            ->count();
    }

    private function getAverageLoadFactor($dateFrom, $dateTo)
    {
        $flights = Flight::with(['aircraft', 'bookings' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereIn('status', ['confirmed', 'confirmed_paid'])
                  ->whereBetween('created_at', [$dateFrom, $dateTo])
                  ->select('flight_id', 'seat_count');
        }])
        ->whereBetween('departure_time', [$dateFrom, $dateTo])
        ->get();

        if ($flights->isEmpty()) {
            return 0;
        }

        $totalLoadFactor = 0;
        $flightCount = 0;

        foreach ($flights as $flight) {
            if (!$flight->aircraft) continue;

            $totalSeats = $flight->aircraft->total_seats;
            if ($totalSeats > 0) {
                $confirmedSeats = $flight->bookings->sum('seat_count');
                $loadFactor = ($confirmedSeats / $totalSeats) * 100;
                $totalLoadFactor += $loadFactor;
                $flightCount++;
            }
        }

        return $flightCount > 0 ? round($totalLoadFactor / $flightCount, 2) : 0;
    }

    private function getAddonRevenue($dateFrom, $dateTo)
    {
        return BookingAddOn::whereHas('booking', function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo])
                  ->whereIn('status', ['confirmed', 'confirmed_paid']);
        })->sum(DB::raw('price * quantity')) ?? 0;
    }

    private function getBookingsPerFlight($dateFrom, $dateTo)
    {
        return Flight::with(['aircraft', 'bookings' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereIn('status', ['confirmed', 'confirmed_paid'])
                  ->whereBetween('created_at', [$dateFrom, $dateTo]);
        }])
        ->whereBetween('departure_time', [$dateFrom, $dateTo])
        ->get()
        ->map(function ($flight) {
            if (!$flight->aircraft) return null;

            $bookings = $flight->bookings;
            $totalSeats = $flight->aircraft->total_seats;
            $seatsSold = $bookings->sum('seat_count');
            $revenue = $bookings->sum('total_price');
            $loadFactor = $totalSeats > 0 ? round(($seatsSold / $totalSeats) * 100, 2) : 0;

            return [
                'flight_number' => $flight->flight_number,
                'route' => $flight->origin . ' → ' . $flight->destination,
                'departure_time' => $flight->departure_time,
                'bookings' => $bookings->count(),
                'seats_sold' => $seatsSold,
                'total_seats' => $totalSeats,
                'load_factor' => $loadFactor,
                'revenue' => $revenue,
            ];
        })
        ->filter()
        ->sortByDesc('revenue')
        ->values();
    }

    private function getRevenueByFareClass($dateFrom, $dateTo)
    {
        return Booking::with('fareClass')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('status', ['confirmed', 'confirmed_paid'])
            ->select('fare_class_id', DB::raw('SUM(total_price) as revenue'), DB::raw('COUNT(*) as bookings'))
            ->groupBy('fare_class_id')
            ->get()
            ->map(function ($item) {
                return [
                    'fare_class' => $item->fareClass->name ?? 'Unknown',
                    'revenue' => $item->revenue,
                    'bookings' => $item->bookings,
                ];
            });
    }

    private function getTopAddons($dateFrom, $dateTo)
    {
        return BookingAddOn::with('addOn')
            ->whereHas('booking', function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo])
                      ->whereIn('status', ['confirmed', 'confirmed_paid']);
            })
            ->select('add_on_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(price * quantity) as total_revenue'))
            ->groupBy('add_on_id')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->addOn->name ?? 'Unknown',
                    'quantity' => $item->total_quantity,
                    'revenue' => $item->total_revenue,
                ];
            });
    }

    public function exportBookings(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $bookings = Booking::with(['flight', 'fareClass', 'user'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'bookings_' . $dateFrom . '_to_' . $dateTo . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($bookings) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Booking ID', 'Status', 'Flight', 'Fare Class', 'Customer', 'Email', 'Seats', 'Total Price', 'Created At']);

            foreach ($bookings as $booking) {
                fputcsv($file, [
                    $booking->booking_reference,
                    ucfirst($booking->status),
                    $booking->flight->flight_number . ' (' . $booking->flight->origin . '-' . $booking->flight->destination . ')',
                    $booking->fareClass->name ?? 'N/A',
                    $booking->user->name ?? $booking->contact_name,
                    $booking->user->email ?? $booking->contact_email,
                    $booking->seat_count,
                    number_format($booking->total_price, 2),
                    $booking->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportRevenue(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $data = $this->getBookingsPerFlight($dateFrom, $dateTo);

        $filename = 'revenue_report_' . $dateFrom . '_to_' . $dateTo . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Flight', 'Route', 'Departure', 'Bookings', 'Seats Sold', 'Total Seats', 'Load Factor (%)', 'Revenue (₱)']);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row['flight_number'],
                    $row['route'],
                    $row['departure_time']->format('Y-m-d H:i'),
                    $row['bookings'],
                    $row['seats_sold'],
                    $row['total_seats'],
                    $row['load_factor'],
                    number_format($row['revenue'], 2),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportAddons(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $addons = $this->getTopAddons($dateFrom, $dateTo);

        $filename = 'addon_sales_' . $dateFrom . '_to_' . $dateTo . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($addons) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Add-on', 'Quantity Sold', 'Revenue (₱)']);

            foreach ($addons as $addon) {
                fputcsv($file, [
                    $addon['name'],
                    $addon['quantity'],
                    number_format($addon['revenue'], 2),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
