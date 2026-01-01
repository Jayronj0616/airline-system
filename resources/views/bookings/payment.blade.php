<x-public-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                            Complete payment within <span id="time-remaining" class="font-bold"></span> or your reservation will expire.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Payment Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-6">Payment Details</h3>

                            <form method="POST" action="{{ route('bookings.payment.process', $booking) }}" id="payment-form">
                                @csrf

                                <!-- Payment Method -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Payment Method <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                            <input type="radio" name="payment_method" value="credit_card" checked required
                                                   class="mr-3" onchange="toggleCardFields()">
                                            <span>Credit Card</span>
                                        </label>
                                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                            <input type="radio" name="payment_method" value="debit_card" required
                                                   class="mr-3" onchange="toggleCardFields()">
                                            <span>Debit Card</span>
                                        </label>
                                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                            <input type="radio" name="payment_method" value="paypal" required
                                                   class="mr-3" onchange="toggleCardFields()">
                                            <span>PayPal</span>
                                        </label>
                                    </div>
                                    @error('payment_method')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Card Details (shown for card payments) -->
                                <div id="card-fields">
                                    <!-- Cardholder Name -->
                                    <div class="mb-4">
                                        <label for="cardholder_name" class="block text-sm font-medium text-gray-700">
                                            Cardholder Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               name="cardholder_name" 
                                               id="cardholder_name"
                                               value="{{ old('cardholder_name') }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('cardholder_name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Card Number -->
                                    <div class="mb-4">
                                        <label for="card_number" class="block text-sm font-medium text-gray-700">
                                            Card Number <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               name="card_number" 
                                               id="card_number"
                                               value="{{ old('card_number') }}"
                                               maxlength="16"
                                               placeholder="1234567890123456"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('card_number')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Expiry and CVV -->
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Expiry Date <span class="text-red-500">*</span>
                                            </label>
                                            <div class="grid grid-cols-2 gap-2">
                                                <input type="number" 
                                                       name="expiry_month" 
                                                       placeholder="MM"
                                                       min="1" max="12"
                                                       value="{{ old('expiry_month') }}"
                                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <input type="number" 
                                                       name="expiry_year" 
                                                       placeholder="YYYY"
                                                       min="2024"
                                                       value="{{ old('expiry_year') }}"
                                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                            @error('expiry_month')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                            @error('expiry_year')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="cvv" class="block text-sm font-medium text-gray-700">
                                                CVV <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="cvv" 
                                                   id="cvv"
                                                   maxlength="3"
                                                   placeholder="123"
                                                   value="{{ old('cvv') }}"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('cvv')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Mock Payment Notice -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                                    <p class="text-sm text-blue-800">
                                        <strong>Note:</strong> This is a demo payment system. Any card details will be accepted. 
                                        In production, this would integrate with a real payment gateway.
                                    </p>
                                </div>

                                <div class="flex justify-between items-center">
                                    <a href="{{ route('bookings.passengers', $booking) }}" 
                                       class="text-gray-600 hover:text-gray-900">
                                        ← Back to Passengers
                                    </a>

                                    <button type="submit" 
                                            class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-8 rounded-lg">
                                        Pay ₱{{ number_format($booking->total_price, 2) }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Booking Summary Sidebar -->
                <div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-4">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Booking Summary</h3>

                            <!-- Flight Details -->
                            <div class="space-y-3 mb-4 pb-4 border-b">
                                <div>
                                    <p class="text-sm text-gray-600">Flight</p>
                                    <p class="font-semibold">{{ $booking->flight->flight_number }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Route</p>
                                    <p class="font-semibold">{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Date</p>
                                    <p class="font-semibold">{{ $booking->flight->departure_time->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Time</p>
                                    <p class="font-semibold">{{ $booking->flight->departure_time->format('h:i A') }}</p>
                                </div>
                            </div>

                            <!-- Passengers -->
                            <div class="mb-4 pb-4 border-b">
                                <p class="text-sm text-gray-600 mb-2">Passengers</p>
                                @foreach($booking->passengers as $passenger)
                                    <p class="text-sm font-medium">{{ $passenger->full_name }}</p>
                                @endforeach
                            </div>

                            <!-- Price Breakdown -->
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">{{ $booking->fareClass->name }} × {{ $booking->seat_count }}</span>
                                    <span>₱{{ number_format($booking->locked_price * $booking->seat_count, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Taxes & Fees</span>
                                    <span>₱0.00</span>
                                </div>
                                <div class="pt-2 border-t">
                                    <div class="flex justify-between font-bold text-lg">
                                        <span>Total</span>
                                        <span class="text-blue-600">₱{{ number_format($booking->total_price, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

            if (minutes < 5) {
                timerAlert.classList.remove('bg-yellow-50', 'border-yellow-400');
                timerAlert.classList.add('bg-red-50', 'border-red-400');
                timerElement.parentElement.classList.remove('text-yellow-700');
                timerElement.parentElement.classList.add('text-red-700');
            }
        }

        updateTimer();
        setInterval(updateTimer, 1000);

        // Toggle card fields based on payment method
        function toggleCardFields() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            const cardFields = document.getElementById('card-fields');
            
            if (paymentMethod === 'paypal') {
                cardFields.style.display = 'none';
                cardFields.querySelectorAll('input').forEach(input => {
                    input.removeAttribute('required');
                });
            } else {
                cardFields.style.display = 'block';
                cardFields.querySelectorAll('input').forEach(input => {
                    if (!input.name.includes('phone') && !input.name.includes('passport')) {
                        input.setAttribute('required', 'required');
                    }
                });
            }
        }
    </script>
    @endpush
</x-public-layout>
