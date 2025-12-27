<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Passenger Information') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(!isset($booking->is_temp) || !$booking->is_temp)
            <!-- Timer Alert -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6" id="timer-alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Complete your booking within <span id="time-remaining" class="font-bold"></span> or your reservation will expire.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Booking Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Booking Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                            <p class="font-semibold">{{ $booking->flight->departure_time->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Class</p>
                            <p class="font-semibold">{{ $booking->fareClass->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Passengers</p>
                            <p class="font-semibold">{{ $booking->seat_count }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Price</p>
                            <p class="font-semibold text-lg text-blue-600">₱{{ number_format($booking->total_price, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fare Rules (Transparency) -->
            @if(isset($fareRules))
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4 text-blue-900">📋 Important Booking Policies</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="bg-white p-4 rounded-lg">
                        <p class="font-semibold text-gray-900 mb-1">Refund</p>
                        <p class="text-gray-700">{{ $fareRules['refundable'] }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg">
                        <p class="font-semibold text-gray-900 mb-1">Changes</p>
                        <p class="text-gray-700">{{ $fareRules['change_fee'] }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg">
                        <p class="font-semibold text-gray-900 mb-1">Baggage</p>
                        <p class="text-gray-700">{{ $fareRules['baggage'] }}</p>
                    </div>
                </div>
                <p class="text-xs text-blue-700 mt-3">By continuing, you agree to these fare conditions.</p>
            </div>
            @endif

            <!-- Passenger Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Enter Passenger Details</h3>

                    <form method="POST" action="{{ route('bookings.passengers.store', $booking->id ?? $booking) }}">
                        @csrf
                        
                        @if(isset($booking->is_temp) && $booking->is_temp)
                        <!-- Contact Information for Guest Booking -->
                        <div class="mb-8 pb-8 border-b-2 border-gray-300">
                            <h4 class="font-semibold mb-4 text-blue-600">Contact & Account Information</h4>
                            <p class="text-sm text-gray-600 mb-4">We'll create an account for you to manage your booking</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="contact_email" class="block text-sm font-medium text-gray-700">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" 
                                           name="contact_email" 
                                           id="contact_email"
                                           value="{{ old('contact_email') }}"
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('contact_email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="contact_password" class="block text-sm font-medium text-gray-700">
                                        Password <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" 
                                           name="contact_password" 
                                           id="contact_password"
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('contact_password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="contact_password_confirmation" class="block text-sm font-medium text-gray-700">
                                        Confirm Password <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" 
                                           name="contact_password_confirmation" 
                                           id="contact_password_confirmation"
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                        @endif

                        @for ($i = 0; $i < $booking->seat_count; $i++)
                            <div class="mb-8 pb-8 border-b border-gray-200 last:border-b-0">
                                <h4 class="font-semibold mb-4">Passenger {{ $i + 1 }}</h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- First Name -->
                                    <div>
                                        <label for="passengers[{{ $i }}][first_name]" class="block text-sm font-medium text-gray-700">
                                            First Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               name="passengers[{{ $i }}][first_name]" 
                                               id="passengers[{{ $i }}][first_name]"
                                               value="{{ old("passengers.$i.first_name") }}"
                                               required
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error("passengers.$i.first_name")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Last Name -->
                                    <div>
                                        <label for="passengers[{{ $i }}][last_name]" class="block text-sm font-medium text-gray-700">
                                            Last Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               name="passengers[{{ $i }}][last_name]" 
                                               id="passengers[{{ $i }}][last_name]"
                                               value="{{ old("passengers.$i.last_name") }}"
                                               required
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error("passengers.$i.last_name")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <label for="passengers[{{ $i }}][email]" class="block text-sm font-medium text-gray-700">
                                            Email <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email" 
                                               name="passengers[{{ $i }}][email]" 
                                               id="passengers[{{ $i }}][email]"
                                               value="{{ old("passengers.$i.email") }}"
                                               required
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error("passengers.$i.email")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Phone -->
                                    <div>
                                        <label for="passengers[{{ $i }}][phone]" class="block text-sm font-medium text-gray-700">
                                            Phone
                                        </label>
                                        <input type="tel" 
                                               name="passengers[{{ $i }}][phone]" 
                                               id="passengers[{{ $i }}][phone]"
                                               value="{{ old("passengers.$i.phone") }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error("passengers.$i.phone")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Date of Birth -->
                                    <div>
                                        <label for="passengers[{{ $i }}][date_of_birth]" class="block text-sm font-medium text-gray-700">
                                            Date of Birth <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" 
                                               name="passengers[{{ $i }}][date_of_birth]" 
                                               id="passengers[{{ $i }}][date_of_birth]"
                                               value="{{ old("passengers.$i.date_of_birth") }}"
                                               required
                                               max="{{ date('Y-m-d') }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error("passengers.$i.date_of_birth")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Passport Number -->
                                    <div>
                                        <label for="passengers[{{ $i }}][passport_number]" class="block text-sm font-medium text-gray-700">
                                            Passport Number
                                        </label>
                                        <input type="text" 
                                               name="passengers[{{ $i }}][passport_number]" 
                                               id="passengers[{{ $i }}][passport_number]"
                                               value="{{ old("passengers.$i.passport_number") }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error("passengers.$i.passport_number")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endfor

                        <div class="flex justify-between items-center mt-6">
                            <a href="{{ route('flights.search') }}" 
                               class="text-gray-600 hover:text-gray-900">
                                ← Back to Search
                            </a>

                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">
                                Continue to Payment →
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(!isset($booking->is_temp) || !$booking->is_temp)
    @push('scripts')
    <script>
        // Countdown timer
        const expiresAt = new Date('{{ $booking->hold_expires_at }}');
        const timerElement = document.getElementById('time-remaining');
        const timerAlert = document.getElementById('timer-alert');

        function updateTimer() {
            const now = new Date();
            const diff = expiresAt - now;

            if (diff <= 0) {
                window.location.href = '{{ route('flights.search') }}';
                return;
            }

            const minutes = Math.floor(diff / 1000 / 60);
            const seconds = Math.floor((diff / 1000) % 60);

            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

            // Change color when less than 5 minutes
            if (minutes < 5) {
                timerAlert.classList.remove('bg-yellow-50', 'border-yellow-400');
                timerAlert.classList.add('bg-red-50', 'border-red-400');
                timerElement.parentElement.classList.remove('text-yellow-700');
                timerElement.parentElement.classList.add('text-red-700');
            }
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    </script>
    @endpush
    @endif
</x-app-layout>
