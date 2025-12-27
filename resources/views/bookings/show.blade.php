<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Booking Details') }} - {{ $booking->booking_reference }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Status Banner -->
            <div class="mb-6 p-4 rounded-lg
                @if($booking->status === 'confirmed') bg-green-50 border border-green-200
                @elseif($booking->status === 'held') bg-yellow-50 border border-yellow-200
                @elseif($booking->status === 'cancelled') bg-red-50 border border-red-200
                @else bg-gray-50 border border-gray-200
                @endif">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($booking->status === 'confirmed')
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif($booking->status === 'held')
                            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @else
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-semibold
                            @if($booking->status === 'confirmed') text-green-800
                            @elseif($booking->status === 'held') text-yellow-800
                            @else text-red-800
                            @endif">
                            Booking Status: {{ strtoupper($booking->status) }}
                        </h3>
                        @if($booking->status === 'held')
                            <p class="text-sm text-yellow-700 mt-1">
                                This booking will expire in {{ $booking->hold_expires_at->diffForHumans() }}. 
                                <a href="{{ route('bookings.passengers', $booking) }}" class="underline font-semibold">Complete now</a>
                            </p>
                        @elseif($booking->status === 'confirmed')
                            <p class="text-sm text-green-700 mt-1">
                                Confirmed on {{ $booking->confirmed_at->format('M d, Y h:i A') }}
                            </p>
                        @elseif($booking->status === 'cancelled')
                            <p class="text-sm text-red-700 mt-1">
                                Cancelled on {{ $booking->cancelled_at->format('M d, Y h:i A') }}
                                @if($booking->cancellation_reason)
                                    - Reason: {{ $booking->cancellation_reason }}
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Booking Details Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-xl font-bold">{{ $booking->flight->flight_number }}</h3>
                            <p class="text-gray-600">{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Booking Reference</p>
                            <p class="font-mono font-bold text-lg">{{ $booking->booking_reference }}</p>
                        </div>
                    </div>

                    <!-- Flight Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 pb-6 border-b">
                        <div>
                            <p class="text-sm text-gray-600">Departure</p>
                            <p class="font-semibold text-lg">{{ $booking->flight->departure_time->format('M d, Y') }}</p>
                            <p class="text-gray-700">{{ $booking->flight->departure_time->format('h:i A') }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $booking->flight->origin }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Arrival</p>
                            <p class="font-semibold text-lg">{{ $booking->flight->arrival_time->format('M d, Y') }}</p>
                            <p class="text-gray-700">{{ $booking->flight->arrival_time->format('h:i A') }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $booking->flight->destination }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Aircraft</p>
                            <p class="font-semibold">{{ $booking->flight->aircraft->model }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Class</p>
                            <p class="font-semibold">{{ $booking->fareClass->name }}</p>
                        </div>
                    </div>

                    <!-- Passengers -->
                    @if($booking->passengers->isNotEmpty())
                        <div class="mb-6 pb-6 border-b">
                            <h4 class="font-semibold mb-4">Passengers</h4>
                            <div class="space-y-3">
                                @foreach($booking->passengers as $passenger)
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-semibold">{{ $passenger->full_name }}</p>
                                            <p class="text-sm text-gray-600">{{ $passenger->email }}</p>
                                            @if($passenger->phone)
                                                <p class="text-sm text-gray-600">{{ $passenger->phone }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <p class="font-semibold text-blue-600">Seat {{ $passenger->seat->seat_number }}</p>
                                            <p class="text-sm text-gray-600">{{ $booking->fareClass->name }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Payment Summary -->
                    <div>
                        <h4 class="font-semibold mb-4">Payment Summary</h4>
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
                                    <span class="
                                        @if($booking->status === 'confirmed') text-green-600
                                        @elseif($booking->status === 'cancelled') text-red-600
                                        @else text-blue-600
                                        @endif">
                                        ₱{{ number_format($booking->total_price, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fare Rules Card (for confirmed bookings) -->
            @if($booking->status === 'confirmed')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h4 class="font-semibold mb-4 text-gray-900">Booking Policies</h4>
                        <div class="space-y-4">
                            <!-- Cancellation Policy -->
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-gray-600 mt-0.5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900">Cancellation</p>
                                        @if($cancellationRules['allowed'])
                                            <p class="text-sm text-gray-700 mt-1">
                                                @if($cancellationRules['fee'] > 0)
                                                    Cancellation fee: <strong>₱{{ number_format($cancellationRules['fee'], 2) }}</strong>
                                                @else
                                                    <span class="text-green-600 font-semibold">Free cancellation</span>
                                                @endif
                                            </p>
                                            @if($cancellationRules['reason'])
                                                <p class="text-xs text-gray-600 mt-1">{{ $cancellationRules['reason'] }}</p>
                                            @endif
                                        @else
                                            <p class="text-sm text-red-600 mt-1">{{ $cancellationRules['reason'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Refund Policy -->
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-gray-600 mt-0.5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900">Refund</p>
                                        @if($refundRules['allowed'])
                                            <p class="text-sm text-gray-700 mt-1">
                                                @if($refundRules['fee'] > 0)
                                                    Refund amount: <strong>₱{{ number_format($refundRules['refund_amount'], 2) }}</strong>
                                                    <span class="text-gray-600">(₱{{ number_format($refundRules['fee'], 2) }} processing fee)</span>
                                                @else
                                                    <span class="text-green-600 font-semibold">Full refund available</span>
                                                @endif
                                            </p>
                                        @else
                                            <p class="text-sm text-red-600 mt-1">{{ $refundRules['reason'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Change Policy -->
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-gray-600 mt-0.5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900">Changes</p>
                                        @if($changeRules['allowed'])
                                            <p class="text-sm text-gray-700 mt-1">
                                                @if($changeRules['fee'] > 0)
                                                    Change fee: <strong>₱{{ number_format($changeRules['fee'], 2) }}</strong>
                                                @else
                                                    <span class="text-green-600 font-semibold">Free changes</span>
                                                @endif
                                            </p>
                                            @if($changeRules['reason'])
                                                <p class="text-xs text-gray-600 mt-1">{{ $changeRules['reason'] }}</p>
                                            @endif
                                        @else
                                            <p class="text-sm text-red-600 mt-1">{{ $changeRules['reason'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('bookings.index') }}" 
                   class="flex-1 bg-white hover:bg-gray-50 text-gray-700 text-center font-semibold py-3 px-6 rounded-lg border border-gray-300">
                    ← Back to My Bookings
                </a>

                @if($booking->status === 'confirmed')
                    <button onclick="window.print()" 
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold py-3 px-6 rounded-lg">
                        Print Booking
                    </button>

                    @if($cancellationRules['allowed'])
                        <a href="{{ route('bookings.cancel.form', $booking) }}" 
                           class="flex-1 bg-red-600 hover:bg-red-700 text-white text-center font-semibold py-3 px-6 rounded-lg">
                            Cancel Booking
                        </a>
                    @endif
                @elseif($booking->status === 'held')
                    <a href="{{ route('bookings.passengers', $booking) }}" 
                       class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center font-semibold py-3 px-6 rounded-lg">
                        Complete Booking
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
