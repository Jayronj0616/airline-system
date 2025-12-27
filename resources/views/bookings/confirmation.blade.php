<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Booking Confirmed') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            <div class="bg-green-50 border-l-4 border-green-400 p-6 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-semibold text-green-800">
                            Booking Confirmed!
                        </h3>
                        <p class="mt-1 text-green-700">
                            Your booking reference is <strong>{{ $booking->booking_reference }}</strong>
                        </p>
                        <p class="mt-1 text-sm text-green-600">
                            A confirmation email has been sent to your registered email address.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Booking Details Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-xl font-bold">{{ $booking->flight->flight_number }}</h3>
                            <p class="text-gray-600">{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</p>
                        </div>
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full">
                            CONFIRMED
                        </span>
                    </div>

                    <!-- Flight Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 pb-6 border-b">
                        <div>
                            <p class="text-sm text-gray-600">Departure</p>
                            <p class="font-semibold text-lg">{{ $booking->flight->departure_time->format('M d, Y') }}</p>
                            <p class="text-gray-700">{{ $booking->flight->departure_time->format('h:i A') }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $booking->flight->origin }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Arrival</p>
                            <p class="font-semibold text-lg">{{ $booking->flight->arrival_time->format('M d, Y') }}</p>
                            <p class="text-gray-700">{{ $booking->flight->arrival_time->format('h:i A') }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $booking->flight->destination }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Aircraft</p>
                            <p class="font-semibold">{{ $booking->flight->aircraft->model }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Class</p>
                            <p class="font-semibold">{{ $booking->fareClass->name }}</p>
                        </div>
                    </div>

                    <!-- Passengers & Seats -->
                    <div class="mb-6 pb-6 border-b">
                        <h4 class="font-semibold mb-4">Passengers & Seat Assignments</h4>
                        <div class="space-y-3">
                            @foreach($booking->passengers as $passenger)
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-semibold">{{ $passenger->full_name }}</p>
                                        <p class="text-sm text-gray-600">{{ $passenger->email }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-blue-600">Seat {{ $passenger->seat->seat_number }}</p>
                                        <p class="text-sm text-gray-600">{{ $booking->fareClass->name }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div>
                        <h4 class="font-semibold mb-4">Payment Summary</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ $booking->fareClass->name }} × {{ $booking->seat_count }}</span>
                                <span>₱{{ number_format($booking->locked_price * $booking->seat_count, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Taxes & Fees</span>
                                <span>₱0.00</span>
                            </div>
                            <div class="pt-2 border-t">
                                <div class="flex justify-between font-bold text-lg">
                                    <span>Total Paid</span>
                                    <span class="text-green-600">₱{{ number_format($booking->total_price, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h4 class="font-semibold text-blue-900 mb-2">Important Information</h4>
                <ul class="space-y-1 text-sm text-blue-800">
                    <li>• Please arrive at the airport at least 2 hours before departure for domestic flights</li>
                    <li>• Bring a valid government-issued ID for check-in</li>
                    <li>• Check-in opens 24 hours before departure</li>
                    <li>• Baggage allowance: Economy (7kg carry-on), Business (10kg carry-on + 23kg checked)</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('bookings.show', $booking) }}" 
                   class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold py-3 px-6 rounded-lg">
                    View Booking Details
                </a>
                <a href="{{ route('flights.search') }}" 
                   class="flex-1 bg-white hover:bg-gray-50 text-gray-700 text-center font-semibold py-3 px-6 rounded-lg border border-gray-300">
                    Book Another Flight
                </a>
                <button onclick="window.print()" 
                        class="flex-1 bg-white hover:bg-gray-50 text-gray-700 text-center font-semibold py-3 px-6 rounded-lg border border-gray-300">
                    Print Confirmation
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
