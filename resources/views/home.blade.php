<x-public-layout>
    <!-- Hero Section with Background Image -->
    <div class="relative h-[600px] bg-cover bg-center" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1920');">
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <!-- Title -->
                <div class="text-center mb-8">
                    <h1 class="text-5xl md:text-6xl font-bold text-white mb-4">
                        Compare and book flights
                    </h1>
                    <p class="text-xl text-white/90">
                        Search hundreds of travel sites at once
                    </p>
                </div>

                <!-- Search Card -->
                <div class="bg-white rounded-2xl shadow-2xl p-6 md:p-8 max-w-5xl mx-auto">
                    <!-- Trip Type Tabs -->
                    <div class="flex gap-4 mb-6 border-b border-gray-200">
                        <button type="button" class="pb-3 px-4 text-blue-600 border-b-2 border-blue-600 font-semibold tab-button" data-trip-type="roundtrip">
                            Round trip
                        </button>
                        <button type="button" class="pb-3 px-4 text-gray-500 hover:text-gray-700 font-semibold tab-button" data-trip-type="oneway">
                            One way
                        </button>
                        <button type="button" class="pb-3 px-4 text-gray-500 hover:text-gray-700 font-semibold tab-button" data-trip-type="multicity">
                            Multi-city
                        </button>
                    </div>

                    <form action="{{ route('flights.search') }}" method="GET">
                        <input type="hidden" name="trip_type" id="trip-type-input" value="roundtrip">
                        
                        <!-- Single/Round Trip Form -->
                        <div id="single-trip-form">
                        <!-- Row 1: From, Swap, To -->
                        <div class="grid grid-cols-1 md:grid-cols-7 gap-4 mb-4">
                            <!-- From -->
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">From</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <input 
                                        type="text" 
                                        name="origin" 
                                        id="origin-input"
                                        placeholder="City or airport"
                                        autocomplete="off"
                                        class="w-full pl-10 pr-4 py-3.5 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0 transition text-gray-900 placeholder-gray-400"
                                        required
                                    >
                                </div>
                            </div>

                            <!-- Swap Button -->
                            <div class="md:col-span-1 flex items-end justify-center pb-3">
                                <button 
                                    type="button" 
                                    id="swap-button"
                                    class="p-3 rounded-full border-2 border-gray-300 hover:border-blue-500 hover:bg-blue-50 transition group"
                                    title="Swap origin and destination"
                                >
                                    <svg class="w-5 h-5 text-gray-600 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- To -->
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">To</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <input 
                                        type="text" 
                                        name="destination" 
                                        id="destination-input"
                                        placeholder="City or airport"
                                        autocomplete="off"
                                        class="w-full pl-10 pr-4 py-3.5 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0 transition text-gray-900 placeholder-gray-400"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Additional Options -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">

                            <!-- Departure Date -->
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Depart</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <input 
                                        type="date" 
                                        name="departure_date" 
                                        id="departure-date"
                                        class="w-full pl-10 pr-4 py-3.5 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0 transition text-gray-900"
                                        required
                                    >
                                </div>
                            </div>

                            <!-- Return Date (only for roundtrip) -->
                            <div class="md:col-span-4" id="return-date-container">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Return</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <input 
                                        type="date" 
                                        name="return_date" 
                                        id="return-date"
                                        class="w-full pl-10 pr-4 py-3.5 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0 transition text-gray-900"
                                        required
                                    >
                                </div>
                            </div>

                            <!-- Cabin Class -->
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cabin class</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <select 
                                        name="cabin_class"
                                        class="w-full pl-10 pr-4 py-3.5 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0 transition text-gray-900 appearance-none"
                                        required
                                    >
                                        <option value="economy">Economy</option>
                                        <option value="premium_economy">Premium Economy</option>
                                        <option value="business">Business</option>
                                        <option value="first">First Class</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Options -->
                        <div class="mb-4">
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                                <input type="checkbox" name="direct_flights" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span>Direct flights only</span>
                            </label>
                        </div>
                        </div>

                        <!-- Multi-City Form -->
                        <div id="multi-city-form" class="hidden">
                            <div id="multi-city-routes">
                                <!-- Flight 1 -->
                                <div class="multi-city-route mb-6">
                                    <h4 class="font-semibold text-gray-700 mb-3">Flight 1</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        <div class="md:col-span-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">From</label>
                                            <input type="text" name="mc_origin[]" placeholder="City or airport" class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
                                        </div>
                                        <div class="md:col-span-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">To</label>
                                            <input type="text" name="mc_destination[]" placeholder="City or airport" class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
                                        </div>
                                        <div class="md:col-span-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Depart</label>
                                            <input type="date" name="mc_date[]" class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
                                        </div>
                                    </div>
                                </div>

                                <!-- Flight 2 -->
                                <div class="multi-city-route mb-6">
                                    <h4 class="font-semibold text-gray-700 mb-3">Flight 2</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        <div class="md:col-span-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">From</label>
                                            <input type="text" name="mc_origin[]" placeholder="City or airport" class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
                                        </div>
                                        <div class="md:col-span-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">To</label>
                                            <input type="text" name="mc_destination[]" placeholder="City or airport" class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
                                        </div>
                                        <div class="md:col-span-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Depart</label>
                                            <input type="date" name="mc_date[]" class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" id="add-flight-btn" class="text-blue-600 hover:text-blue-700 font-semibold mb-4">+ Add another flight</button>
                        </div>

                        <!-- Search Button -->
                        <button 
                            type="submit" 
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 px-8 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 text-lg"
                        >
                            Search flights
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Why Choose Us Section -->
    <div class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why choose us?</h2>
                <p class="text-lg text-gray-600">We make booking flights simple and stress-free</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition">
                    <div class="bg-blue-100 rounded-full w-14 h-14 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Best price guarantee</h3>
                    <p class="text-gray-600 leading-relaxed">Dynamic pricing algorithm ensures you get the most competitive fares based on real-time demand</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition">
                    <div class="bg-green-100 rounded-full w-14 h-14 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Safe & secure</h3>
                    <p class="text-gray-600 leading-relaxed">Enterprise-grade security with proper concurrency handling ensures your booking is protected</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition">
                    <div class="bg-purple-100 rounded-full w-14 h-14 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Instant confirmation</h3>
                    <p class="text-gray-600 leading-relaxed">Real-time inventory management gives you instant booking confirmation in seconds</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Destinations -->
    <div class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Popular destinations</h2>
                <p class="text-lg text-gray-600">Discover amazing places around the world</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Destination 1 -->
                <div class="relative group overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition cursor-pointer h-64">
                    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/70"></div>
                    <img src="https://images.unsplash.com/photo-1555400038-63f5ba517a47?w=800" alt="Tokyo" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    <div class="absolute bottom-0 left-0 right-0 p-6">
                        <h3 class="text-2xl font-bold text-white mb-1">Tokyo</h3>
                        <p class="text-white/90 text-sm">Explore the vibrant capital</p>
                    </div>
                </div>

                <!-- Destination 2 -->
                <div class="relative group overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition cursor-pointer h-64">
                    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/70"></div>
                    <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800" alt="Paris" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    <div class="absolute bottom-0 left-0 right-0 p-6">
                        <h3 class="text-2xl font-bold text-white mb-1">Paris</h3>
                        <p class="text-white/90 text-sm">The city of lights awaits</p>
                    </div>
                </div>

                <!-- Destination 3 -->
                <div class="relative group overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition cursor-pointer h-64">
                    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/70"></div>
                    <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=800" alt="Dubai" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    <div class="absolute bottom-0 left-0 right-0 p-6">
                        <h3 class="text-2xl font-bold text-white mb-1">Dubai</h3>
                        <p class="text-white/90 text-sm">Luxury meets innovation</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Ready to book your next adventure?</h2>
            <p class="text-xl text-blue-100 mb-8">Join thousands of travelers who trust us with their journeys</p>
            <a href="{{ route('register') }}" class="inline-block bg-white text-blue-600 font-bold px-8 py-4 rounded-lg hover:bg-blue-50 transition shadow-lg hover:shadow-xl">
                Sign up now
            </a>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tripTypeInput = document.getElementById('trip-type-input');
            const returnDateContainer = document.getElementById('return-date-container');
            const returnDateInput = document.getElementById('return-date');
            const singleTripForm = document.getElementById('single-trip-form');
            const multiCityForm = document.getElementById('multi-city-form');
            const addFlightBtn = document.getElementById('add-flight-btn');
            const departureDate = document.getElementById('departure-date');
            const swapButton = document.getElementById('swap-button');
            const originInput = document.getElementById('origin-input');
            const destinationInput = document.getElementById('destination-input');
            let routeCounter = 2;

            // Set default dates (today and tomorrow)
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            const nextWeek = new Date(today);
            nextWeek.setDate(nextWeek.getDate() + 7);
            
            departureDate.value = tomorrow.toISOString().split('T')[0];
            returnDateInput.value = nextWeek.toISOString().split('T')[0];
            departureDate.min = today.toISOString().split('T')[0];
            returnDateInput.min = tomorrow.toISOString().split('T')[0];

            // Tab switching
            tabButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tripType = this.dataset.tripType;
                    
                    // Update tab styles
                    tabButtons.forEach(btn => {
                        btn.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600');
                        btn.classList.add('text-gray-500');
                    });
                    this.classList.remove('text-gray-500');
                    this.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');
                    
                    // Update hidden input
                    tripTypeInput.value = tripType;
                    
                    // Show/hide appropriate forms
                    if (tripType === 'multicity') {
                        singleTripForm.classList.add('hidden');
                        multiCityForm.classList.remove('hidden');
                        
                        // Toggle required fields
                        originInput.required = false;
                        destinationInput.required = false;
                        departureDate.required = false;
                        returnDateInput.required = false;
                        document.querySelector('select[name="cabin_class"]').required = false;
                    } else {
                        singleTripForm.classList.remove('hidden');
                        multiCityForm.classList.add('hidden');
                        
                        // Toggle required fields
                        originInput.required = true;
                        destinationInput.required = true;
                        departureDate.required = true;
                        document.querySelector('select[name="cabin_class"]').required = true;
                        
                        // Handle return date visibility
                        if (tripType === 'roundtrip') {
                            returnDateContainer.classList.remove('hidden');
                            returnDateInput.required = true;
                        } else if (tripType === 'oneway') {
                            returnDateContainer.classList.add('hidden');
                            returnDateInput.required = false;
                        }
                    }
                });
            });

            // Update return date min when departure changes
            departureDate.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                selectedDate.setDate(selectedDate.getDate() + 1);
                returnDateInput.min = selectedDate.toISOString().split('T')[0];
                
                // If return date is before new departure, update it
                if (returnDateInput.value && new Date(returnDateInput.value) <= new Date(this.value)) {
                    returnDateInput.value = selectedDate.toISOString().split('T')[0];
                }
            });

            // Swap button
            swapButton.addEventListener('click', function(e) {
                e.preventDefault();
                const temp = originInput.value;
                originInput.value = destinationInput.value;
                destinationInput.value = temp;
            });

            // Add flight for multi-city
            addFlightBtn.addEventListener('click', function() {
                routeCounter++;
                const routesContainer = document.getElementById('multi-city-routes');
                const newRoute = document.createElement('div');
                newRoute.className = 'multi-city-route mb-6';
                newRoute.innerHTML = `
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-semibold text-gray-700">Flight ${routeCounter}</h4>
                        <button type="button" class="remove-flight-btn text-red-600 hover:text-red-700 text-sm font-semibold">× Remove</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">From</label>
                            <input type="text" name="mc_origin[]" placeholder="City or airport" class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">To</label>
                            <input type="text" name="mc_destination[]" placeholder="City or airport" class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Depart</label>
                            <input type="date" name="mc_date[]" min="${today.toISOString().split('T')[0]}" class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
                        </div>
                    </div>
                `;
                routesContainer.appendChild(newRoute);

                newRoute.querySelector('.remove-flight-btn').addEventListener('click', function() {
                    newRoute.remove();
                });
            });

            // Set min date for all existing multi-city date inputs
            document.querySelectorAll('#multi-city-form input[type="date"]').forEach(input => {
                input.min = today.toISOString().split('T')[0];
            });
        });
    </script>
    
    <script src="{{ asset('js/airport-autocomplete.js') }}"></script>
    @endpush
</x-public-layout>
