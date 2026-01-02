<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\Flight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['flight', 'user', 'fareClass', 'passengers']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                  ->orWhereHas('passengers', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('flight', function($q) use ($search) {
                      $q->where('flight_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load([
            'flight.aircraft',
            'user',
            'fareClass',
            'passengers.seat',
            'addOns.addOn',
            'logs.user'
        ]);

        return view('admin.bookings.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Booking is already cancelled');
        }

        DB::beginTransaction();
        try {
            $oldData = $booking->toArray();

            // Cancel booking
            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['reason'],
            ]);

            // Release seats
            foreach ($booking->passengers as $passenger) {
                if ($passenger->seat) {
                    $passenger->seat->release();
                }
            }

            // Update inventory
            $inventory = $booking->flight->getInventoryForFareClass($booking->fare_class_id);
            if ($inventory) {
                $inventory->increment('available_seats', $booking->seat_count);
                $inventory->decrement('booked_seats', $booking->seat_count);
            }

            // Log action
            BookingLog::logAction(
                $booking->id,
                'cancelled',
                "Booking cancelled by admin: {$validated['reason']}",
                $oldData,
                $booking->fresh()->toArray()
            );

            DB::commit();

            return redirect()
                ->route('admin.bookings.show', $booking)
                ->with('success', 'Booking cancelled successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to cancel booking: ' . $e->getMessage());
        }
    }

    public function rebook(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'new_flight_id' => 'required|exists:flights,id',
            'reason' => 'required|string|max:500',
        ]);

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Cannot rebook a cancelled booking');
        }

        $newFlight = Flight::findOrFail($validated['new_flight_id']);

        // Check seat availability
        $inventory = $newFlight->getInventoryForFareClass($booking->fare_class_id);
        if (!$inventory || !$inventory->canBook($booking->seat_count)) {
            return back()->with('error', 'Not enough seats available on the new flight');
        }

        DB::beginTransaction();
        try {
            $oldData = [
                'flight_id' => $booking->flight_id,
                'flight_number' => $booking->flight->flight_number,
            ];

            // Release old seats
            foreach ($booking->passengers as $passenger) {
                if ($passenger->seat) {
                    $passenger->seat->release();
                    $passenger->seat()->dissociate();
                    $passenger->save();
                }
            }

            // Update old flight inventory
            $oldInventory = $booking->flight->getInventoryForFareClass($booking->fare_class_id);
            if ($oldInventory) {
                $oldInventory->increment('available_seats', $booking->seat_count);
                $oldInventory->decrement('booked_seats', $booking->seat_count);
            }

            // Update booking
            $booking->update([
                'flight_id' => $validated['new_flight_id'],
            ]);

            // Update new flight inventory
            $inventory->decrement('available_seats', $booking->seat_count);
            $inventory->increment('booked_seats', $booking->seat_count);

            // Log action
            BookingLog::logAction(
                $booking->id,
                'rebooked',
                "Booking rebooked by admin from flight {$oldData['flight_number']} to {$newFlight->flight_number}: {$validated['reason']}",
                $oldData,
                ['flight_id' => $newFlight->id, 'flight_number' => $newFlight->flight_number]
            );

            DB::commit();

            return redirect()
                ->route('admin.bookings.show', $booking)
                ->with('success', 'Booking rebooked successfully. Passengers need to select new seats.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to rebook: ' . $e->getMessage());
        }
    }

    public function markAsPaid(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($booking->status === 'confirmed') {
            return back()->with('error', 'Booking is already confirmed/paid');
        }

        DB::beginTransaction();
        try {
            $oldData = ['status' => $booking->status];

            $booking->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            // Log action
            BookingLog::logAction(
                $booking->id,
                'marked_paid',
                "Booking manually marked as paid by admin: {$validated['reason']}",
                $oldData,
                ['status' => 'confirmed']
            );

            DB::commit();

            return redirect()
                ->route('admin.bookings.show', $booking)
                ->with('success', 'Booking marked as paid successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to mark as paid: ' . $e->getMessage());
        }
    }

    public function modify(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'contact_name' => 'nullable|string|max:100',
            'reason' => 'required|string|max:500',
        ]);

        $reason = $validated['reason'];
        unset($validated['reason']);

        DB::beginTransaction();
        try {
            $oldData = $booking->only(['contact_email', 'contact_phone', 'contact_name']);

            $booking->update($validated);

            // Log action
            BookingLog::logAction(
                $booking->id,
                'modified',
                "Booking contact details modified by admin: {$reason}",
                $oldData,
                $validated
            );

            DB::commit();

            return redirect()
                ->route('admin.bookings.show', $booking)
                ->with('success', 'Booking updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update booking: ' . $e->getMessage());
        }
    }
}
