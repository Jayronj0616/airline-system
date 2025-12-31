<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FlightStatusController extends Controller
{
    /**
     * Show flight status search page.
     */
    public function index()
    {
        return view('flight-status.index');
    }

    /**
     * Search flight status.
     */
    public function search(Request $request)
    {
        $request->validate([
            'flight_number' => 'nullable|string',
            'origin' => 'nullable|string|size:3',
            'destination' => 'nullable|string|size:3',
            'date' => 'nullable|date',
        ]);

        $query = Flight::query()->with(['aircraft', 'bookings']);

        if ($request->flight_number) {
            $query->where('flight_number', 'LIKE', '%' . strtoupper($request->flight_number) . '%');
        }

        if ($request->origin) {
            $query->where('origin', strtoupper($request->origin));
        }

        if ($request->destination) {
            $query->where('destination', strtoupper($request->destination));
        }

        if ($request->date) {
            $date = Carbon::parse($request->date);
            $query->whereDate('departure_time', $date);
        }

        $flights = $query->orderBy('departure_time')->get();

        // Add status to each flight
        $flights->each(function ($flight) {
            $flight->status_info = $this->calculateFlightStatus($flight);
        });

        return view('flight-status.results', compact('flights'));
    }

    /**
     * Show specific flight status.
     */
    public function show(Flight $flight)
    {
        $flight->load(['aircraft', 'bookings.passengers']);
        $flight->status_info = $this->calculateFlightStatus($flight);

        return view('flight-status.show', compact('flight'));
    }

    /**
     * Calculate flight status based on current time and flight times.
     */
    protected function calculateFlightStatus(Flight $flight)
    {
        $now = Carbon::now();
        $departure = Carbon::parse($flight->departure_time);
        $arrival = Carbon::parse($flight->arrival_time);

        // Calculate boarding time (30 min before departure)
        $boardingTime = $departure->copy()->subMinutes(30);
        // Gate closes (15 min before departure)
        $gateCloseTime = $departure->copy()->subMinutes(15);

        if ($now->lt($boardingTime)) {
            return [
                'status' => 'Scheduled',
                'color' => 'blue',
                'message' => 'Flight is on schedule',
                'icon' => '✓',
            ];
        } elseif ($now->gte($boardingTime) && $now->lt($gateCloseTime)) {
            return [
                'status' => 'Boarding',
                'color' => 'green',
                'message' => 'Boarding in progress',
                'icon' => '→',
            ];
        } elseif ($now->gte($gateCloseTime) && $now->lt($departure)) {
            return [
                'status' => 'Gate Closing',
                'color' => 'orange',
                'message' => 'Final boarding call',
                'icon' => '⚠',
            ];
        } elseif ($now->gte($departure) && $now->lt($arrival)) {
            $progress = $now->diffInMinutes($departure) / $departure->diffInMinutes($arrival) * 100;
            return [
                'status' => 'In Flight',
                'color' => 'purple',
                'message' => 'Currently in flight',
                'icon' => '✈',
                'progress' => round($progress),
            ];
        } elseif ($now->gte($arrival) && $now->lt($arrival->copy()->addHour())) {
            return [
                'status' => 'Landed',
                'color' => 'green',
                'message' => 'Flight has landed',
                'icon' => '↓',
            ];
        } else {
            return [
                'status' => 'Completed',
                'color' => 'gray',
                'message' => 'Flight completed',
                'icon' => '✓',
            ];
        }
    }
}
