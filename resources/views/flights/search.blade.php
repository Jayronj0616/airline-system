<x-public-layout>
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-400 dark:border-green-500 p-4 mb-4">
                <p class="text-green-700 dark:text-green-400">{{ session('success') }}</p>
            </div>
        </div>
    @endif
    
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-400 dark:border-red-500 p-4 mb-4">
                <p class="text-red-700 dark:text-red-400">{{ session('error') }}</p>
            </div>
        </div>
    @endif
    
    @if($errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-400 dark:border-red-500 p-4 mb-4">
                <ul class="list-disc list-inside text-red-700 dark:text-red-400">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Compact Search Form -->
            <form method="GET" action="{{ route('flights.search') }}" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="relative">
                        <input 
                            type="text" 
                            name="origin" 
                            value="{{ request('origin') }}"
                            placeholder="From (e.g. MNL, Manila)"
                            autocomplete="off"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-gray-900 dark:text-white dark:bg-gray-800 placeholder-gray-500 dark:placeholder-gray-400 text-sm"
                        >
                    </div>
                    
                    <div class="relative">
                        <input 
                            type="text" 
                            name="destination" 
                            value="{{ request('destination') }}"
                            placeholder="To (e.g. HKG, Hong Kong)"
                            autocomplete="off"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-gray-900 dark:text-white dark:bg-gray-800 placeholder-gray-500 dark:placeholder-gray-400 text-sm"
                        >
                    </div>
                    
                    <div class="relative">
                        <input 
                            type="date" 
                            name="date" 
                            value="{{ request('date') }}"
                            min="{{ date('Y-m-d') }}"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-gray-900 dark:text-white dark:bg-gray-800 text-sm"
                        >
                    </div>
                </div>
                
                <!-- Advanced Filters -->
                <div class="mt-3">
                    <button type="button" id="toggle-filters" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm font-semibold">
                        + Advanced Filters
                    </button>
                </div>
                
                <div id="advanced-filters" class="hidden mt-3 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Price Range</label>
                            <div class="flex gap-2">
                                <input type="number" name="price_min" value="{{ request('price_min') }}" placeholder="Min" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                                <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="Max" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Departure Time</label>
                            <select name="time_of_day" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                                <option value="">Any time</option>
                                <option value="morning" {{ request('time_of_day') === 'morning' ? 'selected' : '' }}>Morning (6AM-12PM)</option>
                                <option value="afternoon" {{ request('time_of_day') === 'afternoon' ? 'selected' : '' }}>Afternoon (12PM-6PM)</option>
                                <option value="evening" {{ request('time_of_day') === 'evening' ? 'selected' : '' }}>Evening (6PM-12AM)</option>
                                <option value="night" {{ request('time_of_day') === 'night' ? 'selected' : '' }}>Night (12AM-6AM)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fare Class</label>
                            <select name="fare_class" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                                <option value="">All classes</option>
                                @foreach($fareClasses as $fc)
                                    <option value="{{ $fc->id }}" {{ request('fare_class') == $fc->id ? 'selected' : '' }}>{{ $fc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort By</label>
                            <select name="sort_by" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                                <option value="">Default (Time)</option>
                                <option value="price_asc" {{ request('sort_by') === 'price_asc' ? 'selected' : '' }}>Price: Low → High</option>
                                <option value="price_desc" {{ request('sort_by') === 'price_desc' ? 'selected' : '' }}>Price: High → Low</option>
                                <option value="duration_asc" {{ request('sort_by') === 'duration_asc' ? 'selected' : '' }}>Duration: Shortest</option>
                                <option value="duration_desc" {{ request('sort_by') === 'duration_desc' ? 'selected' : '' }}>Duration: Longest</option>
                                <option value="departure_asc" {{ request('sort_by') === 'departure_asc' ? 'selected' : '' }}>Departure: Earliest</option>
                                <option value="departure_desc" {{ request('sort_by') === 'departure_desc' ? 'selected' : '' }}>Departure: Latest</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg transition text-sm">
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="py-8 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(!$flights->isEmpty())
            <!-- Results Header -->
            <div class="mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $flights->total() }} flights found</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Prices updated in real-time</p>
                    </div>
                    @if(request()->hasAny(['price_min', 'price_max', 'time_of_day', 'fare_class', 'sort_by']))
                    <a href="{{ route('flights.search', request()->only(['origin', 'destination', 'date'])) }}" 
                       class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
                        Clear Filters
                    </a>
                    @endif
                </div>
            </div>

            <!-- Fare Class Legend -->
            <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-xl p-5 mb-6">
                <h3 class="font-bold text-blue-900 dark:text-blue-300 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Fare classes explained
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($fareClasses as $fareClass)
                        @php
                            $rules = $fareRules[$fareClass->id] ?? null;
                        @endphp
                        @if($rules)
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                            <h4 class="font-bold text-gray-900 dark:text-white mb-2">{{ $fareClass->name }}</h4>
                            <ul class="space-y-1.5 text-sm text-gray-700 dark:text-gray-300">
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 dark:text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                    </svg>
                                    <span>{{ $rules['baggage'] }}</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 dark:text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                    </svg>
                                    <span>{{ $rules['refundable'] }}</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 dark:text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                    </svg>
                                    <span>{{ $rules['change_fee'] }}</span>
                                </li>
                            </ul>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Flight Results -->
            <div class="space-y-4">
                @foreach($flights as $flight)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <!-- Flight Info -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-4 mb-3">
                                        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-full text-xs font-semibold">
                                            {{ $flight->flight_number }}
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $flight->aircraft->model }}</span>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <!-- Origin -->
                                        <div class="text-left">
                                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $flight->departure_time->format('H:i') }}</p>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $flight->origin }}</p>
                                        </div>
                                        
                                        <!-- Flight Duration Visual -->
                                        <div class="flex-1 mx-6">
                                            <div class="relative">
                                                <div class="border-t-2 border-gray-300 dark:border-gray-600"></div>
                                                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">
                                                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            @php
                                                $duration = $flight->departure_time->diffInMinutes($flight->arrival_time);
                                                $hours = floor($duration / 60);
                                                $minutes = $duration % 60;
                                            @endphp
                                            <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-2">{{ $hours }}h {{ $minutes }}m • Direct</p>
                                        </div>
                                        
                                        <!-- Destination -->
                                        <div class="text-right">
                                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $flight->arrival_time->format('H:i') }}</p>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $flight->destination }}</p>
                                        </div>
                                    </div>
                                    
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">{{ $flight->departure_time->format('D, M j, Y') }}</p>
                                </div>
                            </div>
                            
                            <!-- Pricing Options -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    @foreach($fareClasses as $fareClass)
                                        @php
                                            $key = "{$flight->id}_{$fareClass->id}";
                                            $price = $flightPrices[$key] ?? null;
                                            $trend = $priceTrends[$key] ?? '→';
                                            $updated = $lastUpdated[$key] ?? 'Never';
                                        @endphp
                                        
                                        <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-500 dark:hover:border-blue-400 hover:bg-blue-50/30 dark:hover:bg-blue-900/20 transition cursor-pointer group">
                                            <div class="flex justify-between items-start mb-2">
                                                <p class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase">{{ $fareClass->name }}</p>
                                                @if($price)
                                                    <span class="text-lg font-semibold {{ $trend === '↑' ? 'text-red-600 dark:text-red-400' : ($trend === '↓' ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400') }}">
                                                        {{ $trend }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            @if($price)
                                                <div class="mb-3">
                                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">₱{{ number_format($price, 0) }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">per person</p>
                                                </div>
                                                
                                                <form action="{{ route('booking.create-draft') }}" method="POST" class="mb-2">
                                                    @csrf
                                                    <input type="hidden" name="flight_id" value="{{ $flight->id }}">
                                                    <input type="hidden" name="fare_class_id" value="{{ $fareClass->id }}">
                                                    
                                                    <div class="mb-3">
                                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Passengers</label>
                                                        <select name="passenger_count" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                                            @for($i = 1; $i <= 9; $i++)
                                                                <option value="{{ $i }}">{{ $i }} {{ $i === 1 ? 'passenger' : 'passengers' }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                    
                                                    <button type="submit" class="w-full text-center bg-blue-600 group-hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
                                                        Book Now
                                                    </button>
                                                </form>
                                                
                                                <a href="{{ route('flights.show', $flight) }}" class="block text-center text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                                                    View details
                                                </a>
                                                
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 text-center">Updated {{ $updated }}</p>
                                            @else
                                                <p class="text-lg font-semibold text-red-600 dark:text-red-400 mb-3">Sold out</p>
                                                <button disabled class="w-full bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 font-semibold py-2.5 rounded-lg text-sm cursor-not-allowed">
                                                    Unavailable
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-8">
                {{ $flights->links() }}
            </div>
            @else
                <!-- No Results -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-12 text-center">
                    <svg class="w-20 h-20 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">No flights found</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Try adjusting your search criteria or dates</p>
                    <a href="{{ url('/') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                        Start a new search
                    </a>
                </div>
            @endif
        </div>
    </div>
    
    <script src="{{ asset('js/airport-autocomplete.js') }}"></script>
    <script>
        document.getElementById('toggle-filters').addEventListener('click', function() {
            const filters = document.getElementById('advanced-filters');
            if (filters.classList.contains('hidden')) {
                filters.classList.remove('hidden');
                this.textContent = '- Hide Filters';
            } else {
                filters.classList.add('hidden');
                this.textContent = '+ Advanced Filters';
            }
        });
    </script>
</x-public-layout>
