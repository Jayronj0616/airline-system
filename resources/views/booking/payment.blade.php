@if(auth()->check())
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Payment
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Booking Summary -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4 dark:text-white">Booking Summary</h3>
                        <div class="border dark:border-gray-600 rounded-lg p-4 mb-4">
                            <div class="mb-4">
                                <p class="font-bold text-xl dark:text-white">{{ $booking->flight->flight_number }}</p>
                                <p class="text-gray-600 dark:text-gray-400">{{ $booking->flight->departure_time->format('M d, Y H:i') }}</p>
                                <p class="text-gray-600 dark:text-gray-400">{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</p>
                            </div>
                            <div class="mb-4">
                                <p class="font-semibold dark:text-white">Fare Class: {{ $booking->fareClass->name }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst($booking->fareClass->cabin_type) }}</p>
                            </div>
                        </div>

                        <!-- Passengers -->
                        <div class="border dark:border-gray-600 rounded-lg p-4 mb-4">
                            <h4 class="font-semibold mb-3 dark:text-white">Passengers ({{ $booking->passengers->count() }})</h4>
                            @foreach($booking->passengers as $index => $passenger)
                                <div class="py-3 border-b dark:border-gray-600 last:border-b-0">
                                    <div class="flex justify-between items-start mb-2">
                                        <p class="font-medium text-lg dark:text-white">{{ $passenger->first_name }} {{ $passenger->last_name }}</p>
                                        <span class="text-xs bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300 px-2 py-1 rounded">{{ ucfirst($passenger->gender) }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <div>
                                            <span class="font-medium">Date of Birth:</span> {{ \Carbon\Carbon::parse($passenger->date_of_birth)->format('M d, Y') }}
                                        </div>
                                        <div>
                                            <span class="font-medium">Nationality:</span> {{ $passenger->nationality }}
                                        </div>
                                        <div class="col-span-2">
                                            <span class="font-medium">Passport:</span> {{ $passenger->passport_number }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Contact Information -->
                        <div class="border dark:border-gray-600 rounded-lg p-4 mb-4">
                            <h4 class="font-semibold mb-3 dark:text-white">Contact Information</h4>
                            <p class="text-sm dark:text-gray-300"><span class="font-medium">Name:</span> {{ $booking->contact_name }}</p>
                            <p class="text-sm dark:text-gray-300"><span class="font-medium">Email:</span> {{ $booking->contact_email }}</p>
                            <p class="text-sm dark:text-gray-300"><span class="font-medium">Phone:</span> {{ $booking->contact_phone }}</p>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="border dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700">
                            <h4 class="font-semibold mb-3 dark:text-white">Price Breakdown</h4>
                            <div class="flex justify-between mb-2 dark:text-gray-300">
                                <span>Base Fare ({{ $booking->seat_count }} × ₱{{ number_format($booking->locked_price, 2) }})</span>
                                <span>₱{{ number_format($booking->total_price, 2) }}</span>
                            </div>
                            <div class="flex justify-between font-bold text-lg pt-2 border-t dark:border-gray-600 dark:text-white">
                                <span>Total</span>
                                <span>₱{{ number_format($booking->total_price, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form (Mock) -->
                    <form action="{{ route('booking.payment.process', $booking) }}" method="POST" id="paymentForm">
                        @csrf
                        
                        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg">
                            <p class="text-sm text-blue-800 dark:text-blue-300">
                                <strong>Note:</strong> This is a demo payment system. Click "Confirm Payment" to complete your booking.
                            </p>
                        </div>

                        <div class="flex justify-between">
                            <a href="{{ route('booking.passengers', $booking) }}" class="bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-bold py-2 px-4 rounded">
                                Back
                            </a>
                            <button type="button" onclick="confirmPayment()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                                Confirm Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmPayment() {
            Swal.fire({
                title: 'Confirm Payment?',
                html: 'You are about to pay <strong>₱{{ number_format($booking->total_price, 2) }}</strong> for this booking.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Confirm Payment',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('paymentForm').submit();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
@else
<x-public-layout>
    <div class="py-12 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Booking Summary -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4 dark:text-white">Booking Summary</h3>
                        <div class="border dark:border-gray-600 rounded-lg p-4 mb-4">
                            <div class="mb-4">
                                <p class="font-bold text-xl dark:text-white">{{ $booking->flight->flight_number }}</p>
                                <p class="text-gray-600 dark:text-gray-400">{{ $booking->flight->departure_time->format('M d, Y H:i') }}</p>
                                <p class="text-gray-600 dark:text-gray-400">{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</p>
                            </div>
                            <div class="mb-4">
                                <p class="font-semibold dark:text-white">Fare Class: {{ $booking->fareClass->name }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst($booking->fareClass->cabin_type) }}</p>
                            </div>
                        </div>

                        <!-- Passengers -->
                        <div class="border dark:border-gray-600 rounded-lg p-4 mb-4">
                            <h4 class="font-semibold mb-3 dark:text-white">Passengers ({{ $booking->passengers->count() }})</h4>
                            @foreach($booking->passengers as $index => $passenger)
                                <div class="py-3 border-b dark:border-gray-600 last:border-b-0">
                                    <div class="flex justify-between items-start mb-2">
                                        <p class="font-medium text-lg dark:text-white">{{ $passenger->first_name }} {{ $passenger->last_name }}</p>
                                        <span class="text-xs bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300 px-2 py-1 rounded">{{ ucfirst($passenger->gender) }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <div>
                                            <span class="font-medium">Date of Birth:</span> {{ \Carbon\Carbon::parse($passenger->date_of_birth)->format('M d, Y') }}
                                        </div>
                                        <div>
                                            <span class="font-medium">Nationality:</span> {{ $passenger->nationality }}
                                        </div>
                                        <div class="col-span-2">
                                            <span class="font-medium">Passport:</span> {{ $passenger->passport_number }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Contact Information -->
                        <div class="border dark:border-gray-600 rounded-lg p-4 mb-4">
                            <h4 class="font-semibold mb-3 dark:text-white">Contact Information</h4>
                            <p class="text-sm dark:text-gray-300"><span class="font-medium">Name:</span> {{ $booking->contact_name }}</p>
                            <p class="text-sm dark:text-gray-300"><span class="font-medium">Email:</span> {{ $booking->contact_email }}</p>
                            <p class="text-sm dark:text-gray-300"><span class="font-medium">Phone:</span> {{ $booking->contact_phone }}</p>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="border dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700">
                            <h4 class="font-semibold mb-3 dark:text-white">Price Breakdown</h4>
                            <div class="flex justify-between mb-2 dark:text-gray-300">
                                <span>Base Fare ({{ $booking->seat_count }} × ₱{{ number_format($booking->locked_price, 2) }})</span>
                                <span>₱{{ number_format($booking->total_price, 2) }}</span>
                            </div>
                            <div class="flex justify-between font-bold text-lg pt-2 border-t dark:border-gray-600 dark:text-white">
                                <span>Total</span>
                                <span>₱{{ number_format($booking->total_price, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form (Mock) -->
                    <form action="{{ route('booking.payment.process', $booking) }}" method="POST" id="paymentForm">
                        @csrf
                        
                        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg">
                            <p class="text-sm text-blue-800 dark:text-blue-300">
                                <strong>Note:</strong> This is a demo payment system. Click "Confirm Payment" to complete your booking.
                            </p>
                        </div>

                        <div class="flex justify-between">
                            <a href="{{ route('booking.passengers', $booking) }}" class="bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-bold py-2 px-4 rounded">
                                Back
                            </a>
                            <button type="button" onclick="confirmPayment()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                                Confirm Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        function confirmPayment() {
            Swal.fire({
                title: 'Confirm Payment?',
                html: 'You are about to pay <strong>₱{{ number_format($booking->total_price, 2) }}</strong> for this booking.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Confirm Payment',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('paymentForm').submit();
                }
            });
        }
    </script>
    @endpush
</x-public-layout>
@endif
