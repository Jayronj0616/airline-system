<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\FareClass;
use App\Models\Booking;
use App\Services\InventoryService;
use App\Services\FareRuleService;
use App\Http\Requests\CreateBookingRequest;
use App\Http\Requests\StorePassengersRequest;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Requests\StoreSeatsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    protected $inventoryService;
    protected $fareRuleService;

    public function __construct(InventoryService $inventoryService, FareRuleService $fareRuleService)
    {
        $this->middleware('auth')->only(['index']);
        $this->inventoryService = $inventoryService;
        $this->fareRuleService = $fareRuleService;
    }

    /**
     * Show seat selection page
     */
    public function seats(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->isHoldExpired()) {
            try {
                $booking->expire();
            } catch (\Exception $e) {
                Log::channel('failures')->error('Failed to expire booking', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
            return redirect()->route('flights.search')
                ->with('error', 'Your booking has expired.');
        }

        if ($booking->passengers()->count() === 0) {
            return redirect()->route('bookings.passengers', $booking)
                ->with('error', 'Please enter passenger information first.');
        }

        $booking->load('flight', 'fareClass', 'passengers');
        
        $seats = $booking->flight->seats()
            ->where('fare_class_id', $booking->fareClass->id)
            ->orderBy('seat_number')
            ->get();

        return view('bookings.seats', compact('booking', 'seats'));
    }

    /**
     * Store seat selection
     */
    public function storeSeats(StoreSeatsRequest $request, Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->isHoldExpired()) {
            try {
                $booking->expire();
            } catch (\Exception $e) {
                Log::channel('failures')->error('Failed to expire booking', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
            return redirect()->route('flights.search')
                ->with('error', 'Your booking has expired.');
        }

        try {
            $selectedSeatIds = $request->seats;

            if (count($selectedSeatIds) !== $booking->seat_count) {
                return back()->with('error', "Please select exactly {$booking->seat_count} seat(s).");
            }

            DB::transaction(function () use ($booking, $selectedSeatIds) {
                $passengers = $booking->passengers;

                foreach ($passengers as $index => $passenger) {
                    if (isset($selectedSeatIds[$index])) {
                        $seat = \App\Models\Seat::findOrFail($selectedSeatIds[$index]);
                        
                        if ($seat->status !== 'available') {
                            throw new \Exception('Selected seat is no longer available.');
                        }
                        
                        $passenger->update(['seat_id' => $seat->id]);
                        $seat->hold(15);
                    }
                }
            });

            Log::channel('bookings')->info('Seats selected', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
                'seats' => $selectedSeatIds,
            ]);

            return redirect()->route('bookings.payment', $booking);

        } catch (\Exception $e) {
            Log::channel('failures')->error('Failed to assign seats', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to assign seats. Please try again.');
        }
    }

    /**
     * Step 1: Select flight and fare class (create hold)
     */
    public function create(CreateBookingRequest $request)
    {
        $flight = Flight::findOrFail($request->flight_id);
        $fareClass = FareClass::findOrFail($request->fare_class_id);
        $seatCount = $request->seat_count;

        // Check if flight hasn't departed
        if ($flight->isPast()) {
            Log::channel('bookings')->warning('Attempted booking on past flight', [
                'session_id' => session()->getId(),
                'flight_id' => $flight->id,
                'flight_number' => $flight->flight_number,
            ]);
            return back()->with('error', 'This flight has already departed.');
        }

        // Store booking data in session for guest users
        session([
            'booking_data' => [
                'flight_id' => $flight->id,
                'fare_class_id' => $fareClass->id,
                'seat_count' => $seatCount,
            ]
        ]);

        // Check capacity
        if (!$this->inventoryService->hasCapacity($flight, $fareClass, $seatCount)) {
            $available = $this->inventoryService->getAvailableSeats($flight, $fareClass);
            Log::channel('bookings')->info('Insufficient capacity for booking', [
                'session_id' => session()->getId(),
                'flight_id' => $flight->id,
                'fare_class_id' => $fareClass->id,
                'requested' => $seatCount,
                'available' => $available,
            ]);
            return back()->with('error', "Only {$available} seat(s) available in {$fareClass->name} class. You requested {$seatCount}.");
        }

        try {
            // Generate temporary booking ID to redirect to passenger form
            $tempBookingId = 'temp_' . uniqid();
            session(['temp_booking_id' => $tempBookingId]);

            Log::channel('bookings')->info('Guest booking initiated', [
                'session_id' => session()->getId(),
                'flight_id' => $flight->id,
                'fare_class_id' => $fareClass->id,
                'seat_count' => $seatCount,
            ]);

            return redirect()->route('bookings.passengers', ['booking' => $tempBookingId]);

        } catch (\Exception $e) {
            Log::channel('failures')->error('Failed to initiate booking', [
                'session_id' => session()->getId(),
                'flight_id' => $flight->id,
                'fare_class_id' => $fareClass->id,
                'seat_count' => $seatCount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to initiate booking. Please try again.');
        }
    }



    /**
     * Step 2: Enter passenger information
     */
    public function passengers($booking)
    {
        // Check if this is a temp booking (guest)
        if (str_starts_with($booking, 'temp_')) {
            $bookingData = session('booking_data');
            $tempBookingId = session('temp_booking_id');
            
            if (!$bookingData || $tempBookingId !== $booking) {
                return redirect()->route('flights.search')
                    ->with('error', 'Booking session expired. Please start again.');
            }
            
            $flight = Flight::findOrFail($bookingData['flight_id']);
            $fareClass = FareClass::findOrFail($bookingData['fare_class_id']);
            $seatCount = $bookingData['seat_count'];
            
            // Calculate price
            $pricingService = app(\App\Services\PricingService::class);
            $currentPrice = $pricingService->calculateCurrentPrice($flight, $fareClass);
            $totalPrice = $currentPrice * $seatCount;
            
            // Create a temporary booking object for the view
            $booking = (object) [
                'id' => $booking,
                'flight' => $flight,
                'fareClass' => $fareClass,
                'seat_count' => $seatCount,
                'total_price' => $totalPrice,
                'is_temp' => true,
            ];
            
            $fareRules = $this->fareRuleService->getRuleSummary($fareClass);
            
            return view('bookings.passengers', compact('booking', 'fareRules'));
        }
        
        // Handle existing bookings
        $booking = Booking::findOrFail($booking);
        
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->isHoldExpired()) {
            try {
                $booking->expire();
            } catch (\Exception $e) {
                Log::channel('failures')->error('Failed to expire booking', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
            return redirect()->route('flights.search')
                ->with('error', 'Your booking has expired. Please search for flights again.');
        }

        if ($booking->isConfirmed()) {
            return redirect()->route('bookings.show', $booking);
        }

        $booking->load('flight', 'fareClass');
        $fareRules = $this->fareRuleService->getRuleSummary($booking->fareClass);

        return view('bookings.passengers', compact('booking', 'fareRules'));
    }

    /**
     * Store passenger information
     */
    public function storePassengers(StorePassengersRequest $request, $booking)
    {
        $bookingData = session('booking_data');
        $tempBookingId = session('temp_booking_id');
        
        // Check if this is a temp booking
        if (str_starts_with($booking, 'temp_')) {
            if (!$bookingData || $tempBookingId !== $booking) {
                return redirect()->route('flights.search')
                    ->with('error', 'Booking session expired. Please start again.');
            }
            
            $seatCount = $bookingData['seat_count'];
        } else {
            $booking = Booking::findOrFail($booking);
            
            if ($booking->user_id !== Auth::id()) {
                abort(403);
            }

            if ($booking->isHoldExpired()) {
                try {
                    $booking->expire();
                } catch (\Exception $e) {
                    Log::channel('failures')->error('Failed to expire booking', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
                }
                return redirect()->route('flights.search')
                    ->with('error', 'Your booking has expired. Please search for flights again.');
            }
            
            $seatCount = $booking->seat_count;
        }

        // Validate passengers and contact info
        $validated = $request->validated();

        // Check passenger count matches seat count
        if (count($validated['passengers']) !== $seatCount) {
            return back()->with('error', "Please enter information for exactly {$seatCount} passenger(s).");
        }

        try {
            // If temp booking, create user and actual booking
            if (str_starts_with($booking, 'temp_')) {
                $user = DB::transaction(function () use ($validated, $bookingData) {
                    // Create or get user
                    $user = \App\Models\User::firstOrCreate(
                        ['email' => $validated['contact_email']],
                        [
                            'name' => $validated['passengers'][0]['first_name'] . ' ' . $validated['passengers'][0]['last_name'],
                            'password' => bcrypt($validated['contact_password']),
                        ]
                    );
                    
                    return $user;
                });
                
                // Login the user
                Auth::login($user);
                
                // Create actual booking
                $flight = Flight::findOrFail($bookingData['flight_id']);
                $fareClass = FareClass::findOrFail($bookingData['fare_class_id']);
                $seatCount = $bookingData['seat_count'];
                
                $booking = $this->inventoryService->holdSeats($user, $flight, $fareClass, $seatCount);
            }
            
            DB::transaction(function () use ($booking, $validated) {
                $availableSeats = $this->inventoryService->getAvailableSeatList(
                    $booking->flight,
                    $booking->fareClass
                );

                if ($availableSeats->count() < $booking->seat_count) {
                    throw new \Exception('Not enough seats available. Please try again.');
                }

                foreach ($validated['passengers'] as $index => $passengerData) {
                    $seat = $availableSeats[$index];
                    $seat->hold(15);

                    $booking->passengers()->create([
                        'seat_id' => $seat->id,
                        'first_name' => $passengerData['first_name'],
                        'last_name' => $passengerData['last_name'],
                        'email' => $passengerData['email'],
                        'phone' => $passengerData['phone'] ?? null,
                        'date_of_birth' => $passengerData['date_of_birth'],
                        'passport_number' => $passengerData['passport_number'] ?? null,
                    ]);
                }
            });

            // Clear session data
            session()->forget(['booking_data', 'temp_booking_id']);

            Log::channel('bookings')->info('Passengers added to booking', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
                'passenger_count' => count($validated['passengers']),
            ]);

            return redirect()->route('bookings.seats', $booking);

        } catch (\Exception $e) {
            Log::channel('failures')->error('Failed to store passengers', [
                'session_id' => session()->getId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to save passenger information. Please try again.')->withInput();
        }
    }

    /**
     * Step 3: Payment page (mock payment)
     */
    public function payment(Booking $booking)
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            Log::channel('failures')->warning('Unauthorized payment page access', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
            ]);
            abort(403);
        }

        // Check if booking expired
        if ($booking->isHoldExpired()) {
            try {
                $booking->expire();
            } catch (\Exception $e) {
                Log::channel('failures')->error('Failed to expire booking', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
            return redirect()->route('flights.search')
                ->with('error', 'Your booking has expired. Please search for flights again.');
        }

        // Check if passengers exist
        $passengerCount = $booking->fresh()->passengers()->count();
        if ($passengerCount === 0) {
            return redirect()->route('bookings.passengers', $booking)
                ->with('error', 'Please enter passenger information first.');
        }

        // Check if already confirmed (idempotency check)
        if ($booking->isConfirmed()) {
            return redirect()->route('bookings.confirmation', $booking)
                ->with('info', 'This booking has already been confirmed.');
        }

        $booking->load('flight', 'fareClass', 'passengers');

        return view('bookings.payment', compact('booking'));
    }

    /**
     * Process payment (mock) with idempotency protection
     */
    public function processPayment(ProcessPaymentRequest $request, Booking $booking)
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            Log::channel('failures')->warning('Unauthorized payment attempt', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
            ]);
            abort(403);
        }

        // Idempotency check: If already confirmed, return to confirmation page
        if ($booking->isConfirmed()) {
            Log::channel('payments')->info('Duplicate payment attempt detected (already confirmed)', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
            ]);
            
            return redirect()->route('bookings.confirmation', $booking)
                ->with('info', 'This booking has already been confirmed.');
        }

        // Check if booking expired
        if ($booking->isHoldExpired()) {
            try {
                $booking->expire();
            } catch (\Exception $e) {
                Log::channel('failures')->error('Failed to expire booking during payment', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
            return redirect()->route('flights.search')
                ->with('error', 'Your booking has expired. Please search for flights again.');
        }

        $request->validate([
            'payment_method' => 'required|in:credit_card,debit_card,paypal',
            'cardholder_name' => 'required_if:payment_method,credit_card,debit_card|string|max:255',
            'card_number' => 'required_if:payment_method,credit_card,debit_card|string|size:16',
            'expiry_month' => 'required_if:payment_method,credit_card,debit_card|integer|between:1,12',
            'expiry_year' => 'required_if:payment_method,credit_card,debit_card|integer|min:2024',
            'cvv' => 'required_if:payment_method,credit_card,debit_card|string|size:3',
        ]);

        try {
            Log::channel('payments')->info('Payment processing started', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
                'amount' => $booking->total_price,
                'payment_method' => $request->payment_method,
            ]);

            // Use database transaction for payment processing
            DB::transaction(function () use ($booking) {
                // Mock payment processing (always succeeds)
                // In production, integrate with Stripe/PayPal/etc.
                
                // Confirm booking (this also uses a transaction internally)
                $this->inventoryService->confirmBooking($booking);
            });

            Log::channel('payments')->info('Payment successful', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'amount' => $booking->total_price,
            ]);

            return redirect()->route('bookings.confirmation', $booking)
                ->with('success', 'Payment successful! Your booking is confirmed.');

        } catch (\Exception $e) {
            Log::channel('failures')->error('Payment processing failed', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
                'amount' => $booking->total_price,
                'payment_method' => $request->payment_method,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Payment failed. Please try again.');
        }
    }

    /**
     * Step 4: Confirmation page
     */
    public function confirmation(Booking $booking)
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Ensure booking is confirmed
        if (!$booking->isConfirmed()) {
            return redirect()->route('bookings.show', $booking);
        }

        $booking->load('flight.aircraft', 'fareClass', 'passengers.seat');

        return view('bookings.confirmation', compact('booking'));
    }

    /**
     * View booking details
     */
    public function show(Booking $booking)
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        $booking->load('flight.aircraft', 'fareClass', 'passengers.seat');
        
        // Get cancellation and refund rules
        $cancellationRules = $this->fareRuleService->canCancelBooking($booking);
        $refundRules = $this->fareRuleService->canRefund($booking);
        $changeRules = $this->fareRuleService->canChangeBooking($booking);

        return view('bookings.show', compact('booking', 'cancellationRules', 'refundRules', 'changeRules'));
    }

    /**
     * List user's bookings
     */
    public function index()
    {
        $bookings = Booking::where('user_id', Auth::id())
            ->with(['flight.aircraft', 'fareClass', 'passengers.seat'])
            ->latest()
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Show cancellation confirmation page with fee details
     */
    public function showCancelForm(Booking $booking)
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Check cancellation rules
        $cancellationRules = $this->fareRuleService->canCancelBooking($booking);

        if (!$cancellationRules['allowed']) {
            return back()->with('error', $cancellationRules['reason']);
        }

        $booking->load('flight', 'fareClass', 'passengers');

        return view('bookings.cancel', compact('booking', 'cancellationRules'));
    }

    /**
     * Cancel booking (with fare rule validation)
     */
    public function cancel(Request $request, Booking $booking)
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            Log::channel('failures')->warning('Unauthorized cancellation attempt', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
            ]);
            abort(403);
        }

        // Check cancellation rules
        $cancellationRules = $this->fareRuleService->canCancelBooking($booking);

        if (!$cancellationRules['allowed']) {
            return back()->with('error', $cancellationRules['reason']);
        }

        try {
            DB::transaction(function () use ($booking) {
                $booking->cancel('Cancelled by user');
            });
            
            Log::channel('bookings')->info('Booking cancelled', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'cancellation_fee' => $cancellationRules['fee'],
            ]);
            
            $message = 'Booking cancelled successfully.';
            
            // Add fee information to message if applicable
            if ($cancellationRules['fee'] > 0) {
                $message .= " Cancellation fee: ₱" . number_format($cancellationRules['fee'], 2);
            }
            
            return redirect()->route('bookings.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::channel('failures')->error('Booking cancellation failed', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to cancel booking. Please try again.');
        }
    }

    /**
     * Request refund (for confirmed bookings)
     */
    public function requestRefund(Booking $booking)
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            Log::channel('failures')->warning('Unauthorized refund request', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
            ]);
            abort(403);
        }

        // Check refund rules
        $refundRules = $this->fareRuleService->canRefund($booking);

        if (!$refundRules['allowed']) {
            return back()->with('error', $refundRules['reason']);
        }

        try {
            DB::transaction(function () use ($booking) {
                // Cancel the booking first
                $booking->cancel('Refund requested by user');
            });
            
            Log::channel('payments')->info('Refund requested', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'refund_amount' => $refundRules['refund_amount'],
                'refund_fee' => $refundRules['fee'],
            ]);
            
            // In production, process refund here (Stripe, PayPal, etc.)
            
            $message = 'Refund request submitted.';
            
            if ($refundRules['fee'] > 0) {
                $message .= " Refund amount: ₱" . number_format($refundRules['refund_amount'], 2);
                $message .= " (₱" . number_format($refundRules['fee'], 2) . " processing fee applied)";
            } else {
                $message .= " Full refund: ₱" . number_format($refundRules['refund_amount'], 2);
            }
            
            return redirect()->route('bookings.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::channel('failures')->error('Refund request failed', [
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to process refund request. Please try again.');
        }
    }

    /**
     * Request booking change (placeholder - to be implemented)
     */
    public function requestChange(Booking $booking)
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Check change rules
        $changeRules = $this->fareRuleService->canChangeBooking($booking);

        if (!$changeRules['allowed']) {
            return back()->with('error', $changeRules['reason']);
        }

        // TODO: Implement booking change flow
        // For now, just show the change fee
        $message = 'Booking changes are allowed.';
        
        if ($changeRules['fee'] > 0) {
            $message .= " Change fee: ₱" . number_format($changeRules['fee'], 2);
        } else {
            $message .= " No change fee.";
        }
        
        return back()->with('info', $message . ' (Change functionality coming soon)');
    }
}
