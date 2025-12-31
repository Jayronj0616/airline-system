<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\Booking;
use App\Models\FlightSearch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        // Popular routes - based on bookings
        $popularRoutes = DB::table('bookings')
            ->join('flights', 'bookings.flight_id', '=', 'flights.id')
            ->select(
                'flights.origin',
                'flights.destination',
                DB::raw('COUNT(bookings.id) as booking_count'),
                DB::raw('SUM(bookings.seat_count) as total_passengers'),
                DB::raw('MIN(bookings.total_price / bookings.seat_count) as min_price')
            )
            ->where('bookings.status', 'confirmed')
            ->whereDate('flights.departure_time', '>=', Carbon::now())
            ->groupBy('flights.origin', 'flights.destination')
            ->orderByDesc('booking_count')
            ->limit(6)
            ->get();

        // Trending searches - last 7 days
        $trendingSearches = DB::table('flight_searches')
            ->select(
                'origin',
                'destination',
                DB::raw('COUNT(*) as search_count')
            )
            ->where('searched_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('origin', 'destination')
            ->orderByDesc('search_count')
            ->limit(6)
            ->get();

        // Upcoming flights stats
        $upcomingFlights = Flight::where('departure_time', '>=', Carbon::now())
            ->where('departure_time', '<=', Carbon::now()->addDays(30))
            ->count();

        $availableDestinations = Flight::where('departure_time', '>=', Carbon::now())
            ->distinct('destination')
            ->count('destination');

        return view('home', compact('popularRoutes', 'trendingSearches', 'upcomingFlights', 'availableDestinations'));
    }
}
