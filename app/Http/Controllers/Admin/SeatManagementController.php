<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use App\Models\Seat;
use App\Models\FareClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeatManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Flight::with('aircraft')->where('departure_time', '>', now());
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('flight_number', 'like', "%{$search}%")
                  ->orWhere('origin', 'like', "%{$search}%")
                  ->orWhere('destination', 'like', "%{$search}%");
            });
        }
        
        $flights = $query->orderBy('departure_time')->paginate(20);
        
        return view('admin.seats.index', compact('flights'));
    }

    public function show(Flight $flight)
    {
        $flight->load(['seats.fareClass', 'seats.blockedBy', 'aircraft']);
        
        $fareClasses = FareClass::all();
        
        $seatMap = [];
        foreach ($fareClasses as $fareClass) {
            $seatMap[$fareClass->name] = $flight->seats()
                ->where('fare_class_id', $fareClass->id)
                ->orderBy('seat_number')
                ->get();
        }
        
        $stats = [
            'total' => $flight->seats()->count(),
            'available' => $flight->seats()->where('status', 'available')->count(),
            'booked' => $flight->seats()->where('status', 'booked')->count(),
            'held' => $flight->seats()->where('status', 'held')->count(),
            'blocked_crew' => $flight->seats()->where('status', 'blocked_crew')->count(),
            'blocked_maintenance' => $flight->seats()->where('status', 'blocked_maintenance')->count(),
        ];
        
        return view('admin.seats.show', compact('flight', 'seatMap', 'fareClasses', 'stats'));
    }

    public function block(Request $request, Seat $seat)
    {
        $validated = $request->validate([
            'block_type' => 'required|in:crew,maintenance',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($seat->status === 'booked') {
            return back()->with('error', 'Cannot block a booked seat');
        }

        if ($validated['block_type'] === 'crew') {
            $seat->blockForCrew(Auth::id(), $validated['reason'] ?? null);
        } else {
            $seat->blockForMaintenance(Auth::id(), $validated['reason'] ?? null);
        }

        return back()->with('success', 'Seat ' . $seat->seat_number . ' blocked successfully');
    }

    public function release(Seat $seat)
    {
        if (!$seat->isBlocked()) {
            return back()->with('error', 'Seat is not blocked');
        }

        $seat->releaseBlock();

        return back()->with('success', 'Seat ' . $seat->seat_number . ' released successfully');
    }

    public function bulkBlock(Request $request, Flight $flight)
    {
        $validated = $request->validate([
            'seat_ids' => 'required|array',
            'seat_ids.*' => 'exists:seats,id',
            'block_type' => 'required|in:crew,maintenance',
            'reason' => 'nullable|string|max:255',
        ]);

        $seats = Seat::whereIn('id', $validated['seat_ids'])
            ->where('flight_id', $flight->id)
            ->where('status', '!=', 'booked')
            ->get();

        $count = 0;
        foreach ($seats as $seat) {
            if ($validated['block_type'] === 'crew') {
                $seat->blockForCrew(Auth::id(), $validated['reason'] ?? null);
            } else {
                $seat->blockForMaintenance(Auth::id(), $validated['reason'] ?? null);
            }
            $count++;
        }

        return back()->with('success', "{$count} seat(s) blocked successfully");
    }

    public function bulkRelease(Request $request, Flight $flight)
    {
        $validated = $request->validate([
            'seat_ids' => 'required|array',
            'seat_ids.*' => 'exists:seats,id',
        ]);

        $seats = Seat::whereIn('id', $validated['seat_ids'])
            ->where('flight_id', $flight->id)
            ->whereIn('status', ['blocked_crew', 'blocked_maintenance'])
            ->get();

        $count = 0;
        foreach ($seats as $seat) {
            $seat->releaseBlock();
            $count++;
        }

        return back()->with('success', "{$count} seat(s) released successfully");
    }

    public function uploadSeatMap(Request $request, Flight $flight)
    {
        $validated = $request->validate([
            'seat_map' => 'required|json',
        ]);

        $seatMapData = json_decode($validated['seat_map'], true);
        
        // Validate structure
        if (!isset($seatMapData['seats']) || !is_array($seatMapData['seats'])) {
            return back()->with('error', 'Invalid seat map format');
        }

        // Process seat map
        foreach ($seatMapData['seats'] as $seatData) {
            $fareClass = FareClass::where('code', $seatData['fare_class'])->first();
            
            if (!$fareClass) continue;

            $seat = Seat::where('flight_id', $flight->id)
                ->where('seat_number', $seatData['seat_number'])
                ->first();

            if ($seat) {
                $seat->update(['fare_class_id' => $fareClass->id]);
            } else {
                Seat::create([
                    'flight_id' => $flight->id,
                    'fare_class_id' => $fareClass->id,
                    'seat_number' => $seatData['seat_number'],
                    'status' => 'available',
                ]);
            }
        }

        return back()->with('success', 'Seat map uploaded successfully');
    }
}
