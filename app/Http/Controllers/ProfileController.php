<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $statistics = $user->getStatistics();
        $upcomingTrips = $user->upcomingTrips()->get();
        $savedPaymentMethods = $user->savedPaymentMethods;
        
        return view('profile.edit', [
            'user' => $user,
            'statistics' => $statistics,
            'upcomingTrips' => $upcomingTrips,
            'savedPaymentMethods' => $savedPaymentMethods,
        ]);
    }

    /**
     * Display booking history.
     */
    public function bookingHistory(Request $request): View
    {
        $user = $request->user();
        $pastTrips = $user->pastTrips()->paginate(10);
        $statistics = $user->getStatistics();
        
        return view('profile.booking-history', compact('user', 'pastTrips', 'statistics'));
    }

    /**
     * Download booking history as PDF.
     */
    public function downloadBookingHistory(Request $request)
    {
        $user = $request->user();
        $pastTrips = $user->pastTrips()->get();
        $statistics = $user->getStatistics();
        
        $pdf = Pdf::loadView('profile.booking-history-pdf', compact('user', 'pastTrips', 'statistics'));
        
        return $pdf->download('booking-history-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export booking history as CSV.
     */
    public function exportBookingHistory(Request $request)
    {
        $user = $request->user();
        $pastTrips = $user->pastTrips()->get();
        
        $filename = 'booking-history-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];
        
        $callback = function() use ($pastTrips) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Booking Reference',
                'Flight Number',
                'Origin',
                'Destination',
                'Departure Date',
                'Departure Time',
                'Arrival Time',
                'Fare Class',
                'Passengers',
                'Total Price',
                'Status',
            ]);
            
            // Data
            foreach ($pastTrips as $booking) {
                fputcsv($file, [
                    $booking->booking_reference,
                    $booking->flight->flight_number,
                    $booking->flight->origin,
                    $booking->flight->destination,
                    $booking->flight->departure_time->format('Y-m-d'),
                    $booking->flight->departure_time->format('H:i'),
                    $booking->flight->arrival_time->format('H:i'),
                    $booking->fareClass->name,
                    $booking->passengers->count(),
                    $booking->total_price,
                    $booking->status,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Add route to favorites.
     */
    public function addFavoriteRoute(Request $request): RedirectResponse
    {
        $request->validate([
            'origin' => 'required|string',
            'destination' => 'required|string',
        ]);
        
        $request->user()->addFavoriteRoute(
            $request->origin,
            $request->destination
        );
        
        return back()->with('status', 'route-added-to-favorites');
    }

    /**
     * Remove route from favorites.
     */
    public function removeFavoriteRoute(Request $request): RedirectResponse
    {
        $request->validate([
            'origin' => 'required|string',
            'destination' => 'required|string',
        ]);
        
        $request->user()->removeFavoriteRoute(
            $request->origin,
            $request->destination
        );
        
        return back()->with('status', 'route-removed-from-favorites');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
