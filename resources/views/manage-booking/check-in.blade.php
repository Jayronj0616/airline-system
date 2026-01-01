<x-public-layout>
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">Online Check-In</h1>
                <p class="text-gray-600 mb-8">Booking Reference: <span class="font-semibold">{{ $booking->booking_reference }}</span></p>

                <!-- Flight Summary -->
                <div class="bg-blue-50 rounded-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Flight Summary</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Flight</p>
                            <p class="text-lg font-semibold">{{ $booking->flight->flight_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Departure</p>
                            <p class="text-lg font-semibold">{{ $booking->flight->departure_time->format('M d, Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Route</p>
                            <p class="text-lg font-semibold">{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Passengers</p>
                            <p class="text-lg font-semibold">{{ $booking->passengers->count() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Passengers List -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Passengers</h2>
                    <div class="space-y-3">
                        @foreach($booking->passengers as $passenger)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-semibold">{{ $passenger->full_name }}</p>
                                        @if($passenger->seat)
                                            <p class="text-sm text-gray-600">Seat: {{ $passenger->seat->seat_number }}</p>
                                        @endif
                                    </div>
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Important Information -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                    <h3 class="font-semibold text-gray-900 mb-2">Important Information</h3>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li>• Please arrive at the airport at least 2 hours before departure</li>
                        <li>• Ensure all passenger information is correct</li>
                        <li>• Have your passport/ID ready for verification</li>
                        <li>• Boarding pass will be generated after check-in</li>
                    </ul>
                </div>

                <!-- Check-in Form -->
                <form method="POST" action="{{ route('manage-booking.check-in.process') }}" id="checkInForm">
                    @csrf
                    <input type="hidden" name="booking_reference" value="{{ $booking->booking_reference }}">
                    <input type="hidden" name="last_name" value="{{ $booking->passengers->first()->last_name }}">

                    <div class="mb-6">
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" required class="w-5 h-5 text-blue-600">
                            <span class="text-sm text-gray-700">
                                I confirm that all passenger information is correct and I accept the terms and conditions
                            </span>
                        </label>
                    </div>

                    <div class="flex space-x-4">
                        <a href="{{ route('manage-booking.show') }}?booking_reference={{ $booking->booking_reference }}&last_name={{ $booking->passengers->first()->last_name }}" 
                           class="flex-1 bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg hover:bg-gray-300 text-center">
                            Cancel
                        </a>
                        <button type="button" onclick="confirmCheckIn()" class="flex-1 bg-green-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-green-700">
                            Complete Check-In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function confirmCheckIn() {
            const checkbox = document.querySelector('input[type="checkbox"]');
            if (!checkbox.checked) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Confirmation Required',
                    text: 'Please confirm that all passenger information is correct.',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }
            
            Swal.fire({
                title: 'Complete Check-In?',
                text: 'You are about to complete online check-in. Boarding passes will be generated.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Check In',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('checkInForm').submit();
                }
            });
        }
    </script>
</x-public-layout>
