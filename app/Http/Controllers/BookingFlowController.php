<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\FareClass;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\BookingAddOn;
use App\Services\PricingService;
use App\Mail\BookingConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class BookingFlowController extends Controller
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Step 1: Review selected flight and fare class
     */
    public function reviewFare(Request $request)
    {
        $validated = $request->validate([
            'flight_id' => 'required|exists:flights,id',
            'fare_class_id' => 'required|exists:fare_classes,id',
            'passenger_count' => 'required|integer|min:1|max:9',
        ]);

        $flight = Flight::with(['aircraft', 'seats'])->findOrFail($validated['flight_id']);
        $fareClass = FareClass::with('fareRule')->findOrFail($validated['fare_class_id']);

        // Check if flight has departed
        if ($flight->isPast()) {
            return back()->with('error', 'Cannot book flights that have already departed.');
        }

        // Check availability
        $availableSeats = $flight->availableSeatsForFareClass($fareClass->id);
        if ($availableSeats < $validated['passenger_count']) {
            return back()->with('error', 'Not enough seats available for this fare class.');
        }

        // Calculate price
        $pricePerPerson = $this->pricingService->calculateCurrentPrice($flight, $fareClass);
        $totalPrice = $pricePerPerson * $validated['passenger_count'];

        return view('booking.review-fare', compact(
            'flight',
            'fareClass',
            'pricePerPerson',
            'totalPrice',
            'validated'
        ));
    }

    /**
     * Step 2: Create draft booking
     */
    public function createDraft(Request $request)
    {
        $validated = $request->validate([
            'flight_id' => 'required|exists:flights,id',
            'fare_class_id' => 'required|exists:fare_classes,id',
            'passenger_count' => 'required|integer|min:1|max:9',
        ]);

        $flight = Flight::findOrFail($validated['flight_id']);
        $fareClass = FareClass::findOrFail($validated['fare_class_id']);

        // Check if flight has departed
        if ($flight->isPast()) {
            return back()->with('error', 'Cannot book flights that have already departed.');
        }

        // Check availability again
        $availableSeats = $flight->availableSeatsForFareClass($fareClass->id);
        if ($availableSeats < $validated['passenger_count']) {
            return back()->with('error', 'Not enough seats available.');
        }

        // Lock price and create draft booking
        $lockedPrice = $this->pricingService->calculateCurrentPrice($flight, $fareClass);
        $totalPrice = $lockedPrice * $validated['passenger_count'];

        $booking = Booking::create([
            'user_id' => auth()->id(), // null if guest
            'flight_id' => $flight->id,
            'fare_class_id' => $fareClass->id,
            'status' => 'draft',
            'locked_price' => $lockedPrice,
            'total_price' => $totalPrice,
            'seat_count' => $validated['passenger_count'],
            'held_at' => Carbon::now(),
            'hold_expires_at' => Carbon::now()->addMinutes(15),
        ]);

        return redirect()->route('booking.passengers', $booking);
    }

    /**
     * Step 3: Passenger details form
     */
    public function passengers(Booking $booking)
    {
        if (!$booking->isDraft()) {
            return redirect()->route('booking.show', $booking);
        }

        // Check if flight has departed
        if ($booking->flight->isPast()) {
            return redirect()->route('home')->with('error', 'Cannot complete booking for departed flights.');
        }

        $booking->load(['flight', 'fareClass']);
        
        return view('booking.passengers', compact('booking'));
    }

    /**
     * Step 3: Store passenger details
     */
    public function storePassengers(Request $request, Booking $booking)
    {
        if (!$booking->isDraft()) {
            return redirect()->route('booking.show', $booking);
        }

        // Check if flight has departed
        if ($booking->flight->isPast()) {
            return redirect()->route('home')->with('error', 'Cannot complete booking for departed flights.');
        }

        // Debug: Log the incoming request
        Log::info('Store Passengers Request', [
            'booking_id' => $booking->id,
            'all_data' => $request->all(),
        ]);

        $validated = $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:50',
            'passengers' => 'required|array|min:1',
            'passengers.*.first_name' => 'required|string|max:255',
            'passengers.*.last_name' => 'required|string|max:255',
            'passengers.*.date_of_birth' => 'required|date|before:today',
            'passengers.*.gender' => 'required|in:male,female,other',
            'passengers.*.nationality' => 'required|string|max:3',
            'passengers.*.passport_number' => 'required|string|max:50',
        ]);

        DB::transaction(function () use ($booking, $validated) {
            // Update booking with contact info
            $booking->update([
                'contact_name' => $validated['contact_name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
            ]);

            // Delete existing passengers if any
            $booking->passengers()->delete();

            // Create new passengers
            foreach ($validated['passengers'] as $passengerData) {
                Passenger::create([
                    'booking_id' => $booking->id,
                    'first_name' => $passengerData['first_name'],
                    'last_name' => $passengerData['last_name'],
                    'date_of_birth' => $passengerData['date_of_birth'],
                    'gender' => $passengerData['gender'],
                    'nationality' => $passengerData['nationality'],
                    'passport_number' => $passengerData['passport_number'],
                ]);
            }
        });

        return redirect()->route('booking.payment', $booking);
    }

    /**
     * Step 4: Payment page (simulated)
     */
    public function payment(Booking $booking)
    {
        if (!$booking->isDraft()) {
            return redirect()->route('booking.show', $booking);
        }

        // Check if flight has departed
        if ($booking->flight->isPast()) {
            return redirect()->route('home')->with('error', 'Cannot complete booking for departed flights.');
        }

        $booking->load(['flight', 'fareClass', 'passengers']);
        
        return view('booking.payment', compact('booking'));
    }

    /**
     * Step 4: Process payment (simulated - just confirms booking)
     */
    public function processPayment(Request $request, Booking $booking)
    {
        if (!$booking->isDraft()) {
            return redirect()->route('booking.show', $booking);
        }

        // Check if flight has departed
        if ($booking->flight->isPast()) {
            return redirect()->route('home')->with('error', 'Cannot complete booking for departed flights.');
        }

        DB::transaction(function () use ($booking) {
            $booking->update([
                'status' => 'confirmed_paid',
                'confirmed_at' => Carbon::now(),
            ]);
        });

        // Send confirmation email
        try {
            // Reload booking with relationships for email
            $booking->load(['flight', 'fareClass', 'passengers']);
            
            Mail::to($booking->contact_email)->send(new BookingConfirmation($booking));
            
            Log::info('Booking confirmation email sent', [
                'booking_id' => $booking->id,
                'email' => $booking->contact_email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation email', [
                'booking_id' => $booking->id,
                'email' => $booking->contact_email,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the booking if email fails
        }

        return redirect()->route('booking.confirmation', $booking);
    }

    /**
     * Step 5: Booking confirmation
     */
    public function confirmation(Booking $booking)
    {
        if (!$booking->isConfirmed()) {
            return redirect()->route('booking.show', $booking);
        }

        $booking->load(['flight', 'fareClass', 'passengers']);
        
        return view('booking.confirmation', compact('booking'));
    }

    /**
     * View booking details
     */
    public function show(Booking $booking)
    {
        $booking->load(['flight', 'fareClass', 'passengers', 'addOns']);
        
        return view('booking.show', compact('booking'));
    }

    /**
     * List user's bookings
     */
    public function index()
    {
        $bookings = Booking::with(['flight', 'fareClass'])
            ->where('user_id', auth()->id())
            ->whereIn('status', ['draft', 'confirmed_unpaid', 'confirmed_paid'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('booking.index', compact('bookings'));
    }

    /**
     * Manage booking - seat selection
     */
    public function selectSeats(Booking $booking)
    {
        if (!$booking->isConfirmed()) {
            return redirect()->route('booking.show', $booking)->with('error', 'Booking must be confirmed first.');
        }

        // Check if seats already selected
        if ($booking->passengers()->whereNotNull('seat_id')->exists()) {
            return redirect()->route('booking.show', $booking)->with('error', 'Seats have already been selected for this booking.');
        }

        // Check if flight has departed
        if ($booking->flight->isPast()) {
            return redirect()->route('booking.show', $booking)->with('error', 'Cannot select seats for departed flights.');
        }

        $booking->load(['flight.seats.fareClass', 'passengers']);
        
        return view('booking.select-seats', compact('booking'));
    }

    /**
     * Store seat selections
     */
    public function storeSeats(Request $request, Booking $booking)
    {
        if (!$booking->isConfirmed()) {
            return redirect()->route('booking.show', $booking)->with('error', 'Booking must be confirmed first.');
        }

        // Check if seats already selected
        if ($booking->passengers()->whereNotNull('seat_id')->exists()) {
            return redirect()->route('booking.show', $booking)->with('error', 'Seats have already been selected.');
        }

        // Check if flight has departed
        if ($booking->flight->isPast()) {
            return redirect()->route('booking.show', $booking)->with('error', 'Cannot select seats for departed flights.');
        }

        $validated = $request->validate([
            'seats' => 'required|array',
            'seats.*' => 'required|exists:seats,id',
        ]);

        DB::transaction(function () use ($booking, $validated) {
            foreach ($booking->passengers as $index => $passenger) {
                $seatId = $validated['seats'][$index] ?? null;
                if ($seatId) {
                    $passenger->update(['seat_id' => $seatId]);
                }
            }
        });

        return redirect()->route('booking.show', $booking)->with('success', 'Seats selected successfully.');
    }

    /**
     * Add-ons page
     */
    public function addOns(Booking $booking)
    {
        if (!$booking->isConfirmed()) {
            return redirect()->route('booking.show', $booking);
        }

        $booking->load(['flight', 'passengers', 'addOns']);
        
        return view('booking.add-ons', compact('booking'));
    }

    /**
     * Store add-ons
     */
    public function storeAddOns(Request $request, Booking $booking)
    {
        if (!$booking->isConfirmed()) {
            return redirect()->route('booking.show', $booking)->with('error', 'Booking must be confirmed first.');
        }

        // Filter out zero quantities
        $addOnsData = [];
        foreach ($request->input('add_ons', []) as $type => $data) {
            if (isset($data['quantity']) && $data['quantity'] > 0) {
                $addOnsData[] = [
                    'type' => $data['type'],
                    'quantity' => $data['quantity'],
                    'price' => $data['price'],
                    'description' => ucfirst(str_replace('_', ' ', $type)),
                ];
            }
        }

        if (empty($addOnsData)) {
            return redirect()->route('booking.show', $booking)->with('info', 'No add-ons selected.');
        }

        try {
            DB::transaction(function () use ($booking, $addOnsData) {
                // Create add-ons
                foreach ($addOnsData as $addOnData) {
                    BookingAddOn::create([
                        'booking_id' => $booking->id,
                        'passenger_id' => null,
                        'type' => $addOnData['type'],
                        'description' => $addOnData['description'],
                        'price' => $addOnData['price'],
                        'quantity' => $addOnData['quantity'],
                    ]);
                }
            });

            Log::channel('bookings')->info('Add-ons added to booking', [
                'booking_id' => $booking->id,
                'add_ons_count' => count($addOnsData),
            ]);

            return redirect()->route('booking.show', $booking)->with('success', 'Add-ons saved successfully!');

        } catch (\Exception $e) {
            Log::channel('failures')->error('Failed to add services', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to save add-ons. Please try again.');
        }
    }
}
