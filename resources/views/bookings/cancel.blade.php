<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cancel Booking') }} - {{ $booking->booking_reference }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Warning Banner -->
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex">
                    <svg class="h-6 w-6 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-semibold text-red-800">Are you sure you want to cancel this booking?</h3>
                        <p class="text-sm text-red-700 mt-1">This action cannot be undone. Please review the cancellation details below.</p>
                    </div>
                </div>
            </div>

            <!-- Booking Summary Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="font-semibold text-lg mb-4">Booking Summary</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Flight</span>
                            <span class="font-semibold">{{ $booking->flight->flight_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Route</span>
                            <span class="font-semibold">{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Departure</span>
                            <span class="font-semibold">{{ $booking->flight->departure_time->format('M d, Y h:i A') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Class</span>
                            <span class="font-semibold">{{ $booking->fareClass->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Passengers</span>
                            <span class="font-semibold">{{ $booking->seat_count }} passenger(s)</span>
                        </div>
                        <div class="pt-3 border-t flex justify-between">
                            <span class="text-gray-900 font-semibold">Original Amount Paid</span>
                            <span class="font-bold text-lg">₱{{ number_format($booking->total_price, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancellation Details Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="font-semibold text-lg mb-4">Cancellation Details</h3>
                    
                    <div class="space-y-4">
                        <!-- Cancellation Fee -->
                        <div class="p-4 bg-red-50 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-700 font-medium">Cancellation Fee</span>
                                <span class="text-red-600 font-bold text-lg">
                                    @if($cancellationRules['fee'] > 0)
                                        ₱{{ number_format($cancellationRules['fee'], 2) }}
                                    @else
                                        FREE
                                    @endif
                                </span>
                            </div>
                            @if($cancellationRules['reason'])
                                <p class="text-sm text-gray-600">{{ $cancellationRules['reason'] }}</p>
                            @endif
                        </div>

                        <!-- Refund Amount -->
                        <div class="p-4 bg-green-50 rounded-lg border-2 border-green-200">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700 font-medium">Expected Refund</span>
                                <span class="text-green-600 font-bold text-xl">
                                    ₱{{ number_format($booking->total_price - $cancellationRules['fee'], 2) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mt-2">
                                Refund will be processed to your original payment method within 5-7 business days.
                            </p>
                        </div>

                        <!-- Important Notes -->
                        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <h4 class="font-semibold text-yellow-800 mb-2">Important Notes:</h4>
                            <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                                <li>This cancellation is immediate and cannot be reversed</li>
                                <li>Your seat(s) will be released back to the inventory</li>
                                <li>A cancellation confirmation email will be sent to you</li>
                                <li>Refund processing time may vary depending on your payment provider</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('bookings.show', $booking) }}" 
                   class="flex-1 bg-white hover:bg-gray-50 text-gray-700 text-center font-semibold py-3 px-6 rounded-lg border border-gray-300">
                    ← No, Keep My Booking
                </a>

                <form method="POST" action="{{ route('bookings.cancel', $booking) }}" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            onclick="return confirm('Are you absolutely sure? This action cannot be undone.');"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg">
                        Yes, Cancel My Booking
                    </button>
                </form>
            </div>

            <!-- Passenger List (for reference) -->
            @if($booking->passengers->isNotEmpty())
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="font-semibold mb-4">Affected Passengers</h4>
                        <div class="space-y-2">
                            @foreach($booking->passengers as $passenger)
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="font-medium">{{ $passenger->full_name }}</span>
                                    <span class="text-sm text-gray-600">Seat {{ $passenger->seat->seat_number }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
