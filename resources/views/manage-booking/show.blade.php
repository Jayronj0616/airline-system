<x-public-layout>
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-md p-8 mb-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Booking Details</h1>
                        <p class="text-lg text-gray-600 mt-2">Reference: <span class="font-semibold">{{ $booking->booking_reference }}</span></p>
                        <p class="text-sm text-gray-500">Status: 
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                @elseif($booking->status === 'held') bg-yellow-100 text-yellow-800
                                @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </p>
                    </div>
                    <a href="{{ route('manage-booking.retrieve') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        ← Back to Search
                    </a>
                </div>

                <!-- Flight Details -->
                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Flight Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Flight Number</p>
                            <p class="text-lg font-semibold">{{ $booking->flight->flight_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Aircraft</p>
                            <p class="text-lg font-semibold">{{ $booking->flight->aircraft->model }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">From</p>
                            <p class="text-lg font-semibold">{{ $booking->flight->origin }}</p>
                            <p class="text-sm text-gray-600">{{ $booking->flight->departure_time->format('M d, Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">To</p>
                            <p class="text-lg font-semibold">{{ $booking->flight->destination }}</p>
                            <p class="text-sm text-gray-600">{{ $booking->flight->arrival_time->format('M d, Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fare Class</p>
                            <p class="text-lg font-semibold">{{ $booking->fareClass->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Seats</p>
                            <p class="text-lg font-semibold">{{ $booking->seat_count }}</p>
                        </div>
                    </div>
                </div>

                <!-- Passengers -->
                <div class="border-t border-gray-200 pt-6 mt-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Passengers</h2>
                    <div class="space-y-4">
                        @foreach($booking->passengers as $passenger)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-lg">{{ $passenger->full_name }}</p>
                                        <p class="text-sm text-gray-600">{{ $passenger->email }}</p>
                                        @if($passenger->phone)
                                            <p class="text-sm text-gray-600">{{ $passenger->phone }}</p>
                                        @endif
                                        @if($passenger->seat)
                                            <p class="text-sm text-gray-600 mt-1">Seat: <span class="font-medium">{{ $passenger->seat->seat_number }}</span></p>
                                        @endif
                                    </div>
                                    @if($passenger->hasCheckedIn())
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            ✓ Checked In
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Add-ons -->
                @if($booking->addOns->count() > 0)
                    <div class="border-t border-gray-200 pt-6 mt-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Additional Services</h2>
                        <div class="space-y-2">
                            @foreach($booking->addOns as $addOn)
                                <div class="flex justify-between items-center py-2">
                                    <div>
                                        <p class="font-medium">{{ $addOn->description }}</p>
                                        <p class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $addOn->type)) }}</p>
                                    </div>
                                    <p class="font-semibold">₱{{ number_format($addOn->total_price, 2) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Pricing -->
                <div class="border-t border-gray-200 pt-6 mt-6">
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <p class="text-gray-600">Base Fare</p>
                            <p class="font-semibold">₱{{ number_format($booking->total_price, 2) }}</p>
                        </div>
                        @if($booking->addOns->count() > 0)
                            <div class="flex justify-between items-center">
                                <p class="text-gray-600">Additional Services</p>
                                <p class="font-semibold">₱{{ number_format($booking->add_ons_total, 2) }}</p>
                            </div>
                        @endif
                        <div class="flex justify-between items-center pt-2 border-t border-gray-300">
                            <p class="text-lg font-bold text-gray-900">Total</p>
                            <p class="text-lg font-bold text-gray-900">₱{{ number_format($booking->grand_total, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if($booking->isConfirmed() && !$booking->flight->isPast())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Check-in -->
                    @if($checkInEligibility['allowed'])
                        <form method="GET" action="{{ route('manage-booking.check-in') }}">
                            <input type="hidden" name="booking_reference" value="{{ $booking->booking_reference }}">
                            <input type="hidden" name="last_name" value="{{ $booking->passengers->first()->last_name }}">
                            <button type="submit" class="w-full bg-green-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-green-700 transition duration-200">
                                Check In Now
                            </button>
                        </form>
                    @elseif($booking->isCheckedIn())
                        <form method="GET" action="{{ route('manage-booking.boarding-pass') }}">
                            <input type="hidden" name="booking_reference" value="{{ $booking->booking_reference }}">
                            <input type="hidden" name="last_name" value="{{ $booking->passengers->first()->last_name }}">
                            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                                View Boarding Pass
                            </button>
                        </form>
                    @else
                        <button disabled class="w-full bg-gray-300 text-gray-600 font-semibold py-3 px-4 rounded-lg cursor-not-allowed">
                            Check-in Not Available
                        </button>
                    @endif

                    <!-- Add Services -->
                    <form method="GET" action="{{ route('manage-booking.services') }}">
                        <input type="hidden" name="booking_reference" value="{{ $booking->booking_reference }}">
                        <input type="hidden" name="last_name" value="{{ $booking->passengers->first()->last_name }}">
                        <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                            Add Services
                        </button>
                    </form>

                    <!-- Edit Passengers -->
                    <form method="GET" action="{{ route('manage-booking.edit-passengers') }}">
                        <input type="hidden" name="booking_reference" value="{{ $booking->booking_reference }}">
                        <input type="hidden" name="last_name" value="{{ $booking->passengers->first()->last_name }}">
                        <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                            Edit Passengers
                        </button>
                    </form>

                    <!-- Edit Contact -->
                    <form method="GET" action="{{ route('manage-booking.edit-contact') }}">
                        <input type="hidden" name="booking_reference" value="{{ $booking->booking_reference }}">
                        <input type="hidden" name="last_name" value="{{ $booking->passengers->first()->last_name }}">
                        <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                            Edit Contact
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-public-layout>
