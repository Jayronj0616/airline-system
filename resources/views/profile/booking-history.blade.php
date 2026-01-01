<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Booking History') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('profile.booking-history.download') }}" 
                   class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download PDF
                </a>
                <a href="{{ route('profile.booking-history.export') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Flights</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['total_flights'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Countries Visited</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['countries_visited'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Miles</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($statistics['total_miles']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Spent</p>
                            <p class="text-2xl font-bold text-gray-900">₱{{ number_format($statistics['total_spent'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking History Table -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Past Trips</h3>
                    
                    @if($pastTrips->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passengers</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($pastTrips as $booking)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $booking->booking_reference }}</div>
                                        <div class="text-sm text-gray-500">{{ $booking->flight->flight_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $booking->flight->departure_time->format('M d, Y') }}</div>
                                        <div class="text-sm text-gray-500">{{ $booking->flight->departure_time->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $booking->fareClass->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $booking->passengers->count() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ₱{{ number_format($booking->total_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <form method="POST" action="{{ route('profile.favorite-routes.add') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="origin" value="{{ $booking->flight->origin }}">
                                            <input type="hidden" name="destination" value="{{ $booking->flight->destination }}">
                                            <button type="submit" class="text-blue-600 hover:text-blue-900">
                                                Add to Favorites
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $pastTrips->links() }}
                    </div>
                    @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No booking history</h3>
                        <p class="text-gray-500 mb-6">You haven't completed any trips yet.</p>
                        <a href="{{ route('flights.search') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                            Search Flights
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
