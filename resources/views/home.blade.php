<x-public-layout>
    <!-- Hero Section -->
    <div class="relative h-[600px] bg-cover bg-center" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1920');">
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <div class="text-center mb-8">
                    <h1 class="text-5xl md:text-6xl font-bold text-white mb-4">
                        Your Journey Starts Here
                    </h1>
                    <p class="text-xl text-white/90">
                        Book flights with dynamic pricing and real-time availability
                    </p>
                </div>
                <div class="text-center">
                    <a href="{{ route('flights.search') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-lg text-lg transition">
                        Search Flights →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="py-12 bg-gray-50 dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm p-8 text-center border border-gray-200 dark:border-gray-700">
                    <div class="text-blue-600 dark:text-blue-400 text-5xl mb-4">✈️</div>
                    <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">{{ $upcomingFlights }}</div>
                    <div class="text-gray-600 dark:text-gray-400">Upcoming Flights</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm p-8 text-center border border-gray-200 dark:border-gray-700">
                    <div class="text-green-600 dark:text-green-400 text-5xl mb-4">🌍</div>
                    <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">{{ $availableDestinations }}</div>
                    <div class="text-gray-600 dark:text-gray-400">Destinations</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm p-8 text-center border border-gray-200 dark:border-gray-700">
                    <div class="text-purple-600 dark:text-purple-400 text-5xl mb-4">💰</div>
                    <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">Live</div>
                    <div class="text-gray-600 dark:text-gray-400">Dynamic Pricing</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Routes -->
    @if($popularRoutes->count() > 0)
    <div class="py-16 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">🔥 Popular Routes</h2>
                <p class="text-gray-600 dark:text-gray-400">Most booked destinations this month</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($popularRoutes as $route)
                    <a href="{{ route('flights.search', ['origin' => $route->origin, 'destination' => $route->destination]) }}" 
                       class="block bg-gradient-to-br from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-xl shadow-sm hover:shadow-lg transition p-6 border-2 border-transparent hover:border-blue-500 dark:hover:border-blue-400">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $route->origin }} → {{ $route->destination }}</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">{{ $route->total_passengers }} passengers</p>
                            </div>
                            <div class="bg-white dark:bg-gray-800 rounded-lg px-3 py-1 text-sm font-semibold text-blue-600 dark:text-blue-400 border border-gray-200 dark:border-gray-700">
                                {{ $route->booking_count }} bookings
                            </div>
                        </div>
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Starting from</p>
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">${{ number_format($route->min_price, 0) }}</p>
                            </div>
                            <div class="text-blue-600 dark:text-blue-400 font-semibold">
                                Search →
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Trending Searches -->
    @if($trendingSearches->count() > 0)
    <div class="py-16 bg-gray-50 dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">📈 Trending Searches</h2>
                <p class="text-gray-600 dark:text-gray-400">Where travelers are looking to go</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($trendingSearches as $search)
                    <a href="{{ route('flights.search', ['origin' => $search->origin, 'destination' => $search->destination]) }}" 
                       class="block bg-white dark:bg-gray-900 rounded-xl shadow-sm hover:shadow-lg transition p-6 border-2 border-transparent hover:border-green-500 dark:hover:border-green-400">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                    {{ $search->origin }} → {{ $search->destination }}
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">
                                    🔍 {{ $search->search_count }} searches this week
                                </p>
                            </div>
                            <div class="text-green-600 dark:text-green-400 text-2xl">→</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Features -->
    <div class="py-16 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Why Book With Us?</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-blue-100 dark:bg-blue-900/30 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Dynamic Pricing</h3>
                    <p class="text-gray-600 dark:text-gray-400">Real-time pricing based on demand, availability, and departure time</p>
                </div>
                <div class="text-center">
                    <div class="bg-green-100 dark:bg-green-900/30 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Instant Confirmation</h3>
                    <p class="text-gray-600 dark:text-gray-400">Get your booking confirmed instantly with email notifications</p>
                </div>
                <div class="text-center">
                    <div class="bg-purple-100 dark:bg-purple-900/30 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Easy Management</h3>
                    <p class="text-gray-600 dark:text-gray-400">Manage bookings, check-in online, and track flight status easily</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="py-16 bg-gradient-to-r from-blue-600 to-purple-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold text-white mb-4">Ready to Book Your Next Flight?</h2>
            <p class="text-xl text-white/90 mb-8">Experience smart booking with our advanced airline system</p>
            <div class="flex gap-4 justify-center">
                <a href="{{ route('flights.search') }}" class="bg-white text-blue-600 font-bold py-3 px-8 rounded-lg hover:bg-gray-100 transition">
                    Search Flights
                </a>
                <a href="{{ route('flight-status.index') }}" class="bg-transparent border-2 border-white text-white font-bold py-3 px-8 rounded-lg hover:bg-white/10 transition">
                    Check Flight Status
                </a>
            </div>
        </div>
    </div>
</x-public-layout>
