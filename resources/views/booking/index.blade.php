<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            My Bookings
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($bookings->count() > 0)
                <div class="space-y-4">
                    @foreach($bookings as $booking)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                            <div class="p-6">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-4 mb-2">
                                            <h3 class="text-xl font-bold">{{ $booking->booking_reference }}</h3>
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                                @if($booking->status === 'confirmed_paid') bg-green-100 text-green-800
                                                @elseif($booking->status === 'confirmed_unpaid') bg-yellow-100 text-yellow-800
                                                @elseif($booking->status === 'confirmed') bg-green-100 text-green-800
                                                @elseif($booking->status === 'draft') bg-blue-100 text-blue-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                @if($booking->status === 'confirmed_paid')
                                                    ✓ CONFIRMED
                                                @else
                                                    {{ strtoupper(str_replace('_', ' ', $booking->status)) }}
                                                @endif
                                            </span>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                            <div>
                                                <p class="text-sm text-gray-600">Flight</p>
                                                <p class="font-semibold">{{ $booking->flight->flight_number }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Route</p>
                                                <p class="font-semibold">{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Departure</p>
                                                <p class="font-semibold">{{ $booking->flight->departure_time->format('M d, Y H:i') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Passengers</p>
                                                <p class="font-semibold">{{ $booking->seat_count }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Fare Class</p>
                                                <p class="font-semibold">{{ $booking->fareClass->name }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Total</p>
                                                <p class="font-semibold">${{ number_format($booking->total_price, 2) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <a href="{{ route('booking.show', $booking) }}" 
                                            class="inline-block px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $bookings->links() }}
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Bookings Yet</h3>
                        <p class="text-gray-600 mb-6">You haven't made any bookings. Start by searching for flights!</p>
                        <a href="{{ route('flights.search') }}" 
                            class="inline-block px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded">
                            Search Flights
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
