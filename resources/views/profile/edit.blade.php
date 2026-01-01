<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Profile') }}
            </h2>
            <a href="{{ route('profile.booking-history') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                View Booking History
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Statistics Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
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
                            <p class="text-sm font-medium text-gray-500">Countries</p>
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

            <!-- Upcoming Trips -->
            @if($upcomingTrips->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Trips</h3>
                    <div class="space-y-4">
                        @foreach($upcomingTrips->take(3) as $booking)
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">{{ $booking->booking_reference }}</p>
                                    <p class="text-lg font-bold text-gray-900 mt-1">
                                        {{ $booking->flight->origin }} → {{ $booking->flight->destination }}
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $booking->flight->departure_time->format('M d, Y • H:i') }}
                                    </p>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                    {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Favorite Routes -->
            @if($user->favorite_routes && count($user->favorite_routes) > 0)
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Favorite Routes</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($user->favorite_routes as $route)
                        <div class="border border-gray-200 rounded-lg p-4 flex justify-between items-center">
                            <div>
                                <p class="font-bold text-gray-900">{{ $route['origin'] }} → {{ $route['destination'] }}</p>
                                <a href="{{ route('flights.search', ['origin' => $route['origin'], 'destination' => $route['destination']]) }}" 
                                   class="text-sm text-blue-600 hover:text-blue-700">
                                    Quick Book
                                </a>
                            </div>
                            <form method="POST" action="{{ route('profile.favorite-routes.remove') }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="origin" value="{{ $route['origin'] }}">
                                <input type="hidden" name="destination" value="{{ $route['destination'] }}">
                                <button type="submit" class="text-red-600 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Profile Information -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Update Password -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Delete Account -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
