<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Bookings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($bookings->isEmpty())
                <!-- Empty State -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-2 text-lg font-semibold text-gray-900">No bookings yet</h3>
                        <p class="mt-1 text-gray-500">Start by searching for flights.</p>
                        <div class="mt-6">
                            <a href="{{ route('flights.search') }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                                Search Flights
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Bookings List -->
                <div class="space-y-4">
                    @foreach($bookings as $booking)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                            <div class="p-6">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                    <!-- Booking Info -->
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="text-lg font-semibold">{{ $booking->flight->flight_number }}</h3>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                                @elseif($booking->status === 'held') bg-yellow-100 text-yellow-800
                                                @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ strtoupper($booking->status) }}
                                            </span>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                            <div>
                                                <p class="text-gray-600">Route</p>
                                                <p class="font-medium">{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600">Departure</p>
                                                <p class="font-medium">{{ $booking->flight->departure_time->format('M d, Y h:i A') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600">Passengers</p>
                                                <p class="font-medium">{{ $booking->seat_count }} × {{ $booking->fareClass->name }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <p class="text-sm text-gray-600">
                                                Booking Reference: <span class="font-mono font-semibold">{{ $booking->booking_reference }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="mt-4 md:mt-0 md:ml-6 flex flex-col gap-2">
                                        <div class="text-right mb-2">
                                            <p class="text-2xl font-bold text-blue-600">₱{{ number_format($booking->total_price, 2) }}</p>
                                        </div>

                                        @if($booking->status === 'held')
                                            <a href="{{ route('bookings.passengers', $booking) }}" 
                                               class="inline-flex justify-center items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-semibold rounded-lg">
                                                Complete Booking
                                            </a>
                                            <p class="text-xs text-red-600 text-center">
                                                Expires in {{ $booking->hold_expires_at->diffForHumans() }}
                                            </p>
                                        @elseif($booking->status === 'confirmed')
                                            <a href="{{ route('bookings.show', $booking) }}" 
                                               class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
                                                View Details
                                            </a>
                                            @if($booking->canBeCancelled())
                                                <form method="POST" action="{{ route('bookings.cancel', $booking) }}" 
                                                      onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg">
                                                        Cancel Booking
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <a href="{{ route('bookings.show', $booking) }}" 
                                               class="inline-flex justify-center items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-lg">
                                                View Details
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
