<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use App\Models\Aircraft;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FlightController extends Controller
{
    public function index()
    {
        $flights = Flight::with('aircraft')
            ->orderBy('departure_time', 'desc')
            ->paginate(20);
        
        $aircraft = Aircraft::all();
            
        return view('admin.flights.index', compact('flights', 'aircraft'));
    }

    public function create()
    {
        $aircraft = Aircraft::all();
        return view('admin.flights.create', compact('aircraft'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'flight_number' => 'required|string|max:10|unique:flights',
            'aircraft_id' => 'required|exists:aircraft,id',
            'origin' => 'required|string|size:3',
            'destination' => 'required|string|size:3',
            'departure_time' => 'required|date|after:now',
            'arrival_time' => 'required|date|after:departure_time',
            'base_price_economy' => 'required|integer|min:1',
            'base_price_business' => 'required|integer|min:1',
            'base_price_first' => 'required|integer|min:1',
            'tax_percentage' => 'required|numeric|min:0|max:100',
            'booking_fee' => 'required|numeric|min:0',
            'fuel_surcharge' => 'required|numeric|min:0',
            'status' => 'required|in:scheduled,delayed,cancelled',
        ]);

        DB::beginTransaction();
        try {
            $flight = Flight::create($validated);
            
            // Auto-generate seats based on aircraft
            $this->generateSeatsForFlight($flight);
            
            DB::commit();
            
            return redirect()
                ->route('admin.flights.index')
                ->with('success', 'Flight created successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create flight: ' . $e->getMessage());
        }
    }

    public function edit(Flight $flight)
    {
        $aircraft = Aircraft::all();
        return view('admin.flights.edit', compact('flight', 'aircraft'));
    }

    public function update(Request $request, Flight $flight)
    {
        $validated = $request->validate([
            'flight_number' => 'required|string|max:10|unique:flights,flight_number,' . $flight->id,
            'aircraft_id' => 'required|exists:aircraft,id',
            'origin' => 'required|string|size:3',
            'destination' => 'required|string|size:3',
            'departure_time' => 'required|date',
            'arrival_time' => 'required|date|after:departure_time',
            'base_price_economy' => 'required|integer|min:1',
            'base_price_business' => 'required|integer|min:1',
            'base_price_first' => 'required|integer|min:1',
            'tax_percentage' => 'required|numeric|min:0|max:100',
            'booking_fee' => 'required|numeric|min:0',
            'fuel_surcharge' => 'required|numeric|min:0',
            'status' => 'required|in:scheduled,delayed,cancelled',
        ]);

        DB::beginTransaction();
        try {
            $oldAircraftId = $flight->aircraft_id;
            $flight->update($validated);
            
            // If aircraft changed, regenerate seats
            if ($oldAircraftId != $validated['aircraft_id']) {
                $this->regenerateSeatsForFlight($flight);
            }
            
            DB::commit();
            
            return redirect()
                ->route('admin.flights.index')
                ->with('success', 'Flight updated successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update flight: ' . $e->getMessage());
        }
    }

    public function destroy(Flight $flight)
    {
        // Check if flight has bookings
        if ($flight->bookings()->exists()) {
            return back()->with('error', 'Cannot delete flight with existing bookings. Cancel it instead.');
        }

        DB::beginTransaction();
        try {
            // Delete seats first
            $flight->seats()->delete();
            $flight->delete();
            
            DB::commit();
            
            return redirect()
                ->route('admin.flights.index')
                ->with('success', 'Flight deleted successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete flight: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Flight $flight)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,delayed,cancelled,boarding,departed,arrived',
        ]);

        $flight->update(['status' => $validated['status']]);

        return back()->with('success', 'Flight status updated to ' . $validated['status']);
    }

    private function generateSeatsForFlight(Flight $flight)
    {
        $aircraft = $flight->aircraft;
        $fareClasses = \App\Models\FareClass::all();

        foreach ($fareClasses as $fareClass) {
            $seatCount = match($fareClass->name) {
                'Economy' => $aircraft->economy_seats,
                'Business' => $aircraft->business_seats,
                'First Class' => $aircraft->first_class_seats,
                default => 0,
            };

            // Create inventory record
            \App\Models\FlightFareInventory::create([
                'flight_id' => $flight->id,
                'fare_class_id' => $fareClass->id,
                'total_seats' => $seatCount,
                'available_seats' => $seatCount,
                'booked_seats' => 0,
                'held_seats' => 0,
            ]);

            // Create seat records
            for ($i = 1; $i <= $seatCount; $i++) {
                Seat::create([
                    'flight_id' => $flight->id,
                    'fare_class_id' => $fareClass->id,
                    'seat_number' => $this->generateSeatNumber($fareClass->name, $i),
                    'status' => 'available',
                ]);
            }
        }
    }

    private function regenerateSeatsForFlight(Flight $flight)
    {
        // Delete old inventory
        $flight->fareInventory()->delete();
        
        // Delete old seats that are not booked
        $flight->seats()->where('status', 'available')->delete();
        
        // Generate new seats and inventory
        $this->generateSeatsForFlight($flight);
    }

    private function generateSeatNumber($fareClassName, $index)
    {
        $prefix = match($fareClassName) {
            'First Class' => 'F',
            'Business' => 'B',
            'Economy' => 'E',
            default => 'S',
        };

        return $prefix . str_pad($index, 2, '0', STR_PAD_LEFT);
    }
}
