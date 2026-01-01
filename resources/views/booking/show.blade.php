<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Booking Details - {{ $booking->booking_reference }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Booking Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Status Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-2xl font-bold">{{ $booking->booking_reference }}</h3>
                                    <p class="text-gray-600 mt-1">Booking Status</p>
                                </div>
                                <span class="px-4 py-2 rounded-full text-sm font-semibold
                                    @if($booking->status === 'confirmed_paid') bg-green-100 text-green-800
                                    @elseif($booking->status === 'confirmed_unpaid') bg-yellow-100 text-yellow-800
                                    @elseif($booking->status === 'confirmed') bg-green-100 text-green-800
                                    @elseif($booking->status === 'draft') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    @if($booking->status === 'confirmed_paid')
                                        ✓ CONFIRMED
                                    @else
                                        {{ strtoupper(str_replace('_', ' ', $booking->status)) }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Flight Details -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Flight Details</h3>
                            <div class="border rounded-lg p-4">
                                <div class="mb-4">
                                    <p class="font-bold text-xl">{{ $booking->flight->flight_number }}</p>
                                    <p class="text-gray-600">{{ $booking->flight->aircraft->name ?? 'N/A' }}</p>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-2xl font-bold">{{ $booking->flight->origin }}</p>
                                        <p class="text-sm text-gray-600">{{ $booking->flight->departure_time->format('M d, Y H:i') }}</p>
                                    </div>
                                    <div class="px-4">
                                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                                        </svg>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl font-bold">{{ $booking->flight->destination }}</p>
                                        <p class="text-sm text-gray-600">{{ $booking->flight->arrival_time->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="mt-4 pt-4 border-t">
                                    <p class="font-semibold">Fare Class: {{ $booking->fareClass->name }}</p>
                                    <p class="text-sm text-gray-600">{{ ucfirst($booking->fareClass->cabin_type) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Passengers -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Passengers ({{ $booking->passengers->count() }})</h3>
                            <div class="space-y-3">
                                @foreach($booking->passengers as $index => $passenger)
                                    <div class="border rounded-lg p-4">
                                        <div class="flex justify-between">
                                            <div>
                                                <p class="font-semibold">{{ $passenger->first_name }} {{ $passenger->last_name }}</p>
                                                <p class="text-sm text-gray-600">DOB: {{ $passenger->date_of_birth->format('M d, Y') }}</p>
                                                <p class="text-sm text-gray-600">{{ ucfirst($passenger->gender) }} | {{ $passenger->nationality }}</p>
                                            </div>
                                            @if($passenger->seat)
                                                <div class="text-right">
                                                    <p class="text-sm text-gray-600">Seat</p>
                                                    <p class="font-bold text-lg">{{ $passenger->seat->seat_number }}</p>
                                                </div>
                                            @else
                                                <div class="text-right">
                                                    <p class="text-sm text-gray-600">No seat assigned</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Add-ons -->
                    @if($booking->addOns->count() > 0)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Add-ons & Services</h3>
                                <div class="space-y-2">
                                    @foreach($booking->addOns as $addOn)
                                        <div class="flex justify-between items-center border-b pb-2">
                                            <div>
                                                <p class="font-semibold">{{ ucfirst(str_replace('_', ' ', $addOn->type)) }}</p>
                                                <p class="text-sm text-gray-600">Quantity: {{ $addOn->quantity }}</p>
                                            </div>
                                            <p class="font-semibold">${{ number_format($addOn->price * $addOn->quantity, 2) }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Price Summary -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Price Summary</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>Fare ({{ $booking->seat_count }} × ${{ number_format($booking->locked_price, 2) }})</span>
                                    <span class="font-semibold">${{ number_format($booking->locked_price * $booking->seat_count, 2) }}</span>
                                </div>
                                @if($booking->addOns->count() > 0)
                                    <div class="flex justify-between">
                                        <span>Add-ons</span>
                                        <span class="font-semibold">${{ number_format($booking->add_ons_total, 2) }}</span>
                                    </div>
                                @endif
                                <hr class="my-3">
                                <div class="flex justify-between text-lg font-bold">
                                    <span>Total</span>
                                    <span>${{ number_format($booking->grand_total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($booking->isConfirmed())
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Manage Booking</h3>
                                <div class="space-y-3">
                                    @auth
                                        @php
                                            $seatsAlreadySelected = $booking->passengers()->whereNotNull('seat_id')->exists();
                                            $canSelectSeats = !$seatsAlreadySelected && !$booking->flight->isPast();
                                            $canAddServices = $booking->flight->hours_until_departure > 3 && !$booking->flight->isPast();
                                        @endphp
                                        
                                        @if($canSelectSeats)
                                            <a href="{{ route('booking.select-seats', $booking) }}" 
                                                class="block w-full text-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded">
                                                Select Seats
                                            </a>
                                        @else
                                            <button disabled
                                                class="block w-full text-center px-4 py-2 bg-gray-300 text-gray-600 font-semibold rounded cursor-not-allowed">
                                                @if($seatsAlreadySelected)
                                                    Seats Already Selected
                                                @else
                                                    Select Seats Unavailable
                                                @endif
                                            </button>
                                        @endif
                                        
                                        @if($canAddServices)
                                            <a href="{{ route('booking.add-ons', $booking) }}" 
                                                class="block w-full text-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded">
                                                Add Services
                                            </a>
                                        @else
                                            <button disabled
                                                class="block w-full text-center px-4 py-2 bg-gray-300 text-gray-600 font-semibold rounded cursor-not-allowed">
                                                Add Services Unavailable
                                            </button>
                                        @endif
                                    @endauth
                                    
                                    @php
                                        $canCheckIn = $booking->flight->hours_until_departure > 3 && !$booking->flight->isPast();
                                    @endphp
                                    @if($canCheckIn)
                                        <a href="{{ route('manage-booking.check-in') }}?booking_reference={{ $booking->booking_reference }}" 
                                            class="block w-full text-center px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white font-semibold rounded">
                                            Check In
                                        </a>
                                    @else
                                        <button disabled
                                            class="block w-full text-center px-4 py-2 bg-gray-300 text-gray-600 font-semibold rounded cursor-not-allowed">
                                            Check In Unavailable
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Contact Info -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Contact Information</h3>
                            <div class="space-y-2 text-sm">
                                <p class="font-semibold">{{ $booking->contact_name }}</p>
                                <p class="text-gray-600">{{ $booking->contact_email }}</p>
                                <p class="text-gray-600">{{ $booking->contact_phone }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
