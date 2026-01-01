@auth
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Booking Confirmed
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Success Message -->
                    <div class="mb-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Booking Confirmed!</h2>
                        <p class="text-gray-600">Your booking reference: <span class="font-bold text-lg">{{ $booking->booking_reference }}</span></p>
                        <p class="text-sm text-gray-500 mt-2">A confirmation email has been sent to {{ $booking->contact_email }}</p>
                    </div>

                    <!-- Flight Details -->
                    <div class="mb-6 border rounded-lg p-4">
                        <h3 class="font-semibold text-lg mb-3">Flight Details</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Flight Number:</span>
                                <span class="font-semibold">{{ $booking->flight->flight_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Route:</span>
                                <span class="font-semibold">{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Departure:</span>
                                <span class="font-semibold">{{ $booking->flight->departure_time->format('M d, Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Arrival:</span>
                                <span class="font-semibold">{{ $booking->flight->arrival_time->format('M d, Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Fare Class:</span>
                                <span class="font-semibold">{{ $booking->fareClass->name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Passengers -->
                    <div class="mb-6 border rounded-lg p-4">
                        <h3 class="font-semibold text-lg mb-3">Passengers ({{ $booking->passengers->count() }})</h3>
                        @foreach($booking->passengers as $passenger)
                            <div class="py-3 {{ !$loop->last ? 'border-b' : '' }}">
                                <p class="font-semibold text-base mb-2">{{ $passenger->first_name }} {{ $passenger->last_name }}</p>
                                <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                                    @if($passenger->date_of_birth)
                                    <div><span class="font-medium">Date of Birth:</span> {{ $passenger->date_of_birth->format('M d, Y') }}</div>
                                    @endif
                                    @if($passenger->gender)
                                    <div><span class="font-medium">Gender:</span> {{ ucfirst($passenger->gender) }}</div>
                                    @endif
                                    @if($passenger->nationality)
                                    <div><span class="font-medium">Nationality:</span> {{ $passenger->nationality }}</div>
                                    @endif
                                    @if($passenger->passport_number)
                                    <div><span class="font-medium">Passport:</span> {{ $passenger->passport_number }}</div>
                                    @endif
                                    @if($passenger->seat)
                                    <div><span class="font-medium">Seat:</span> {{ $passenger->seat->seat_number }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Contact Information -->
                    <div class="mb-6 border rounded-lg p-4">
                        <h3 class="font-semibold text-lg mb-3">Contact Information</h3>
                        <div class="space-y-1 text-sm">
                            <p><span class="text-gray-600">Name:</span> {{ $booking->contact_name }}</p>
                            <p><span class="text-gray-600">Email:</span> {{ $booking->contact_email }}</p>
                            <p><span class="text-gray-600">Phone:</span> {{ $booking->contact_phone }}</p>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="mb-6 border rounded-lg p-4 bg-gray-50">
                        <h3 class="font-semibold text-lg mb-3">Payment Summary</h3>
                        <div class="flex justify-between mb-2">
                            <span>Total Amount Paid:</span>
                            <span class="font-bold text-lg">₱{{ number_format($booking->total_price, 2) }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Payment Status: <span class="text-green-600 font-semibold">Confirmed</span></p>
                    </div>

                    <!-- Next Steps -->
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h3 class="font-semibold mb-2">Next Steps</h3>
                        <ul class="list-disc list-inside text-sm space-y-1 text-gray-700">
                            <li>Check your email for the booking confirmation</li>
                            <li>Arrive at the airport at least 2 hours before departure</li>
                            <li>Bring a valid ID matching the passenger name</li>
                            <li>Check-in online 24 hours before your flight</li>
                        </ul>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('home') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded text-center">
                            Book Another Flight
                        </a>
                        @auth
                        <a href="{{ route('booking.show', $booking) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded text-center">
                            View Booking Details
                        </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
@endauth

@guest
<x-public-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Success Message -->
                    <div class="mb-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Booking Confirmed!</h2>
                        <p class="text-gray-600">Your booking reference: <span class="font-bold text-lg">{{ $booking->booking_reference }}</span></p>
                        <p class="text-sm text-gray-500 mt-2">A confirmation email has been sent to {{ $booking->contact_email }}</p>
                    </div>

                    <!-- Flight Details -->
                    <div class="mb-6 border rounded-lg p-4">
                        <h3 class="font-semibold text-lg mb-3">Flight Details</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Flight Number:</span>
                                <span class="font-semibold">{{ $booking->flight->flight_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Route:</span>
                                <span class="font-semibold">{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Departure:</span>
                                <span class="font-semibold">{{ $booking->flight->departure_time->format('M d, Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Arrival:</span>
                                <span class="font-semibold">{{ $booking->flight->arrival_time->format('M d, Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Fare Class:</span>
                                <span class="font-semibold">{{ $booking->fareClass->name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Passengers -->
                    <div class="mb-6 border rounded-lg p-4">
                        <h3 class="font-semibold text-lg mb-3">Passengers ({{ $booking->passengers->count() }})</h3>
                        @foreach($booking->passengers as $passenger)
                            <div class="py-3 {{ !$loop->last ? 'border-b' : '' }}">
                                <p class="font-semibold text-base mb-2">{{ $passenger->first_name }} {{ $passenger->last_name }}</p>
                                <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                                    @if($passenger->date_of_birth)
                                    <div><span class="font-medium">Date of Birth:</span> {{ $passenger->date_of_birth->format('M d, Y') }}</div>
                                    @endif
                                    @if($passenger->gender)
                                    <div><span class="font-medium">Gender:</span> {{ ucfirst($passenger->gender) }}</div>
                                    @endif
                                    @if($passenger->nationality)
                                    <div><span class="font-medium">Nationality:</span> {{ $passenger->nationality }}</div>
                                    @endif
                                    @if($passenger->passport_number)
                                    <div><span class="font-medium">Passport:</span> {{ $passenger->passport_number }}</div>
                                    @endif
                                    @if($passenger->seat)
                                    <div><span class="font-medium">Seat:</span> {{ $passenger->seat->seat_number }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Contact Information -->
                    <div class="mb-6 border rounded-lg p-4">
                        <h3 class="font-semibold text-lg mb-3">Contact Information</h3>
                        <div class="space-y-1 text-sm">
                            <p><span class="text-gray-600">Name:</span> {{ $booking->contact_name }}</p>
                            <p><span class="text-gray-600">Email:</span> {{ $booking->contact_email }}</p>
                            <p><span class="text-gray-600">Phone:</span> {{ $booking->contact_phone }}</p>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="mb-6 border rounded-lg p-4 bg-gray-50">
                        <h3 class="font-semibold text-lg mb-3">Payment Summary</h3>
                        <div class="flex justify-between mb-2">
                            <span>Total Amount Paid:</span>
                            <span class="font-bold text-lg">₱{{ number_format($booking->total_price, 2) }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Payment Status: <span class="text-green-600 font-semibold">Confirmed</span></p>
                    </div>

                    <!-- Next Steps -->
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h3 class="font-semibold mb-2">Next Steps</h3>
                        <ul class="list-disc list-inside text-sm space-y-1 text-gray-700">
                            <li>Check your email for the booking confirmation</li>
                            <li>Arrive at the airport at least 2 hours before departure</li>
                            <li>Bring a valid ID matching the passenger name</li>
                            <li>Check-in online 24 hours before your flight</li>
                        </ul>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('home') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded text-center">
                            Book Another Flight
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
@endguest
