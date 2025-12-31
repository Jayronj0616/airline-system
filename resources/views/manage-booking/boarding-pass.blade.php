<x-public-layout>
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Check-In Complete!</h1>
                    <p class="text-gray-600">Your boarding passes are ready</p>
                </div>

                <!-- Boarding Passes -->
                @foreach($booking->boardingPasses as $boardingPass)
                    <div class="mb-6 border-2 border-blue-600 rounded-lg overflow-hidden">
                        <!-- Header -->
                        <div class="bg-blue-600 text-white p-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm opacity-90">Boarding Pass</p>
                                    <p class="text-2xl font-bold">{{ $booking->flight->flight_number }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm opacity-90">Booking Reference</p>
                                    <p class="text-lg font-semibold">{{ $booking->booking_reference }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Passenger Info -->
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-6 mb-6">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Passenger Name</p>
                                    <p class="text-xl font-bold text-gray-900">{{ $boardingPass->passenger->full_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Seat</p>
                                    <p class="text-xl font-bold text-gray-900">{{ $boardingPass->passenger->seat->seat_number ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <!-- Flight Details -->
                            <div class="grid grid-cols-3 gap-4 mb-6 pb-6 border-b border-gray-200">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">From</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ $booking->flight->origin }}</p>
                                    <p class="text-sm text-gray-600">{{ $booking->flight->departure_time->format('M d, H:i') }}</p>
                                </div>
                                <div class="text-center">
                                    <svg class="w-8 h-8 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500 mb-1">To</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ $booking->flight->destination }}</p>
                                    <p class="text-sm text-gray-600">{{ $booking->flight->arrival_time->format('M d, H:i') }}</p>
                                </div>
                            </div>

                            <!-- Boarding Info -->
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Gate</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ $boardingPass->gate }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Boarding Time</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($boardingPass->boarding_time)->format('H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Boarding Group</p>
                                    <p class="text-lg font-semibold text-gray-900">Group {{ $boardingPass->boarding_group }}</p>
                                </div>
                            </div>

                            <!-- Barcode -->
                            <div class="bg-gray-100 rounded-lg p-4">
                                <p class="text-center text-xs text-gray-600 mb-2">Barcode</p>
                                <div class="font-mono text-center text-lg tracking-widest">{{ $boardingPass->barcode }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Actions -->
                <div class="space-y-4">
                    <button onclick="window.print()" class="w-full bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-blue-700">
                        Print Boarding Pass
                    </button>
                    <a href="{{ route('manage-booking.show') }}?booking_reference={{ $booking->booking_reference }}&last_name={{ $booking->passengers->first()->last_name }}" 
                       class="block w-full bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg hover:bg-gray-300 text-center">
                        Back to Booking
                    </a>
                </div>

                <!-- Important Notes -->
                <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Important Reminders</h3>
                    <ul class="space-y-1 text-sm text-gray-700">
                        <li>• Arrive at the gate 30 minutes before boarding time</li>
                        <li>• Bring a valid government-issued ID</li>
                        <li>• Keep this boarding pass with you at all times</li>
                        <li>• Check baggage allowance before heading to the airport</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .bg-white, .bg-white * {
                visibility: visible;
            }
            .bg-white {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            button, .bg-gray-200, .bg-yellow-50 {
                display: none !important;
            }
        }
    </style>
</x-public-layout>
