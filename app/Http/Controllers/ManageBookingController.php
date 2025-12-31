<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingAddOn;
use App\Services\CheckInService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageBookingController extends Controller
{
    protected $checkInService;

    public function __construct(CheckInService $checkInService)
    {
        $this->checkInService = $checkInService;
    }

    /**
     * Show retrieve booking form.
     */
    public function retrieve()
    {
        return view('manage-booking.retrieve');
    }

    /**
     * Retrieve booking by reference and last name.
     */
    public function show(Request $request)
    {
        $request->validate([
            'booking_reference' => 'required|string',
            'last_name' => 'required|string',
        ]);

        $booking = Booking::where('booking_reference', strtoupper($request->booking_reference))
            ->whereHas('passengers', function ($query) use ($request) {
                $query->where('last_name', 'LIKE', $request->last_name);
            })
            ->with(['flight.aircraft', 'fareClass', 'passengers.seat', 'addOns', 'checkIns', 'boardingPasses'])
            ->first();

        if (!$booking) {
            return back()->with('error', 'Booking not found. Please check your booking reference and last name.');
        }

        // Check if can check-in
        $checkInEligibility = $this->checkInService->canCheckIn($booking);
        $checkInStatus = $this->checkInService->getCheckInStatus($booking);

        return view('manage-booking.show', compact('booking', 'checkInEligibility', 'checkInStatus'));
    }

    /**
     * Show add services page.
     */
    public function services(Request $request)
    {
        $booking = $this->retrieveBooking($request);
        
        if (!$booking) {
            return redirect()->route('manage-booking.retrieve')
                ->with('error', 'Booking not found.');
        }

        if (!$booking->isConfirmed()) {
            return back()->with('error', 'Only confirmed bookings can add services.');
        }

        $prices = BookingAddOn::getPrices();

        return view('manage-booking.services', compact('booking', 'prices'));
    }

    /**
     * Store add-ons.
     */
    public function storeServices(Request $request)
    {
        $booking = $this->retrieveBooking($request);
        
        if (!$booking) {
            return redirect()->route('manage-booking.retrieve')
                ->with('error', 'Booking not found.');
        }

        $request->validate([
            'services' => 'required|array',
            'services.*.type' => 'required|in:baggage,meal,seat_upgrade,insurance,priority_boarding,lounge_access',
            'services.*.description' => 'required|string',
            'services.*.price' => 'required|numeric|min:0',
            'services.*.quantity' => 'required|integer|min:1',
            'services.*.passenger_id' => 'nullable|exists:passengers,id',
        ]);

        try {
            DB::transaction(function () use ($booking, $request) {
                foreach ($request->services as $service) {
                    BookingAddOn::create([
                        'booking_id' => $booking->id,
                        'passenger_id' => $service['passenger_id'] ?? null,
                        'type' => $service['type'],
                        'description' => $service['description'],
                        'price' => $service['price'],
                        'quantity' => $service['quantity'],
                    ]);
                }
            });

            Log::channel('bookings')->info('Services added to booking', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'services_count' => count($request->services),
            ]);

            return redirect()->route('manage-booking.show', [
                'booking_reference' => $booking->booking_reference,
                'last_name' => $booking->passengers->first()->last_name,
            ])->with('success', 'Services added successfully!');

        } catch (\Exception $e) {
            Log::channel('failures')->error('Failed to add services', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to add services. Please try again.');
        }
    }

    /**
     * Show check-in page.
     */
    public function checkIn(Request $request)
    {
        $booking = $this->retrieveBooking($request);
        
        if (!$booking) {
            return redirect()->route('manage-booking.retrieve')
                ->with('error', 'Booking not found.');
        }

        $checkInEligibility = $this->checkInService->canCheckIn($booking);

        if (!$checkInEligibility['allowed']) {
            return back()->with('error', $checkInEligibility['reason']);
        }

        return view('manage-booking.check-in', compact('booking'));
    }

    /**
     * Process check-in.
     */
    public function processCheckIn(Request $request)
    {
        $booking = $this->retrieveBooking($request);
        
        if (!$booking) {
            return redirect()->route('manage-booking.retrieve')
                ->with('error', 'Booking not found.');
        }

        try {
            $this->checkInService->checkIn($booking);

            Log::channel('bookings')->info('Check-in completed', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
            ]);

            return redirect()->route('manage-booking.boarding-pass', [
                'booking_reference' => $booking->booking_reference,
                'last_name' => $booking->passengers->first()->last_name,
            ])->with('success', 'Check-in successful!');

        } catch (\Exception $e) {
            Log::channel('failures')->error('Check-in failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show boarding pass.
     */
    public function boardingPass(Request $request)
    {
        $booking = $this->retrieveBooking($request);
        
        if (!$booking) {
            return redirect()->route('manage-booking.retrieve')
                ->with('error', 'Booking not found.');
        }

        if (!$booking->isCheckedIn()) {
            return back()->with('error', 'Please check-in first to view boarding passes.');
        }

        $booking->load('boardingPasses.passenger.seat');

        return view('manage-booking.boarding-pass', compact('booking'));
    }

    /**
     * Download boarding pass (PDF placeholder).
     */
    public function downloadBoardingPass(Request $request)
    {
        $booking = $this->retrieveBooking($request);
        
        if (!$booking) {
            return redirect()->route('manage-booking.retrieve')
                ->with('error', 'Booking not found.');
        }

        // TODO: Generate PDF using dompdf or similar
        return back()->with('info', 'PDF download feature coming soon. Please print this page for now.');
    }

    /**
     * Show edit passengers page.
     */
    public function editPassengers(Request $request)
    {
        $booking = $this->retrieveBooking($request);
        
        if (!$booking) {
            return redirect()->route('manage-booking.retrieve')
                ->with('error', 'Booking not found.');
        }

        if (!$booking->isConfirmed()) {
            return back()->with('error', 'Only confirmed bookings can update passenger info.');
        }

        return view('manage-booking.edit-passengers', compact('booking'));
    }

    /**
     * Update passenger information.
     */
    public function updatePassengers(Request $request)
    {
        $booking = $this->retrieveBooking($request);
        
        if (!$booking) {
            return redirect()->route('manage-booking.retrieve')
                ->with('error', 'Booking not found.');
        }

        $request->validate([
            'passengers' => 'required|array',
            'passengers.*.id' => 'required|exists:passengers,id',
            'passengers.*.first_name' => 'required|string|max:255',
            'passengers.*.last_name' => 'required|string|max:255',
            'passengers.*.email' => 'required|email',
            'passengers.*.phone' => 'nullable|string|max:20',
            'passengers.*.passport_number' => 'nullable|string|max:50',
        ]);

        try {
            DB::transaction(function () use ($booking, $request) {
                foreach ($request->passengers as $passengerData) {
                    $passenger = $booking->passengers()->find($passengerData['id']);
                    if ($passenger) {
                        $passenger->update([
                            'first_name' => $passengerData['first_name'],
                            'last_name' => $passengerData['last_name'],
                            'email' => $passengerData['email'],
                            'phone' => $passengerData['phone'] ?? null,
                            'passport_number' => $passengerData['passport_number'] ?? null,
                        ]);
                    }
                }
            });

            Log::channel('bookings')->info('Passenger info updated', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
            ]);

            return redirect()->route('manage-booking.show', [
                'booking_reference' => $booking->booking_reference,
                'last_name' => $booking->passengers->first()->last_name,
            ])->with('success', 'Passenger information updated successfully!');

        } catch (\Exception $e) {
            Log::channel('failures')->error('Failed to update passenger info', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to update passenger information. Please try again.');
        }
    }

    /**
     * Show edit contact page.
     */
    public function editContact(Request $request)
    {
        $booking = $this->retrieveBooking($request);
        
        if (!$booking) {
            return redirect()->route('manage-booking.retrieve')
                ->with('error', 'Booking not found.');
        }

        return view('manage-booking.edit-contact', compact('booking'));
    }

    /**
     * Update contact information.
     */
    public function updateContact(Request $request)
    {
        $booking = $this->retrieveBooking($request);
        
        if (!$booking) {
            return redirect()->route('manage-booking.retrieve')
                ->with('error', 'Booking not found.');
        }

        $request->validate([
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
        ]);

        try {
            DB::transaction(function () use ($booking, $request) {
                $booking->user->update([
                    'email' => $request->email,
                ]);

                // Update first passenger's contact as well
                $firstPassenger = $booking->passengers->first();
                if ($firstPassenger) {
                    $firstPassenger->update([
                        'email' => $request->email,
                        'phone' => $request->phone,
                    ]);
                }
            });

            Log::channel('bookings')->info('Contact info updated', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
            ]);

            return redirect()->route('manage-booking.show', [
                'booking_reference' => $booking->booking_reference,
                'last_name' => $booking->passengers->first()->last_name,
            ])->with('success', 'Contact information updated successfully!');

        } catch (\Exception $e) {
            Log::channel('failures')->error('Failed to update contact info', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to update contact information. Please try again.');
        }
    }

    /**
     * Helper: Retrieve booking from request params.
     */
    protected function retrieveBooking(Request $request)
    {
        return Booking::where('booking_reference', strtoupper($request->booking_reference))
            ->whereHas('passengers', function ($query) use ($request) {
                $query->where('last_name', 'LIKE', $request->last_name);
            })
            ->with(['flight.aircraft', 'fareClass', 'passengers.seat', 'addOns', 'checkIns', 'boardingPasses'])
            ->first();
    }
}
