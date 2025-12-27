<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Flight Details') }} - {{ $flight->flight_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Flight Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Flight Information</h3>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Flight Number:</dt>
                                    <dd class="font-semibold">{{ $flight->flight_number }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Aircraft:</dt>
                                    <dd class="font-semibold">{{ $flight->aircraft->model }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Route:</dt>
                                    <dd class="font-semibold">{{ $flight->origin }} → {{ $flight->destination }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Departure:</dt>
                                    <dd class="font-semibold">{{ $flight->departure_time->format('M d, Y h:i A') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Arrival:</dt>
                                    <dd class="font-semibold">{{ $flight->arrival_time->format('M d, Y h:i A') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Status:</dt>
                                    <dd>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($flight->status === 'scheduled') bg-green-100 text-green-800
                                            @elseif($flight->status === 'boarding') bg-blue-100 text-blue-800
                                            @elseif($flight->status === 'delayed') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($flight->status) }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Capacity</h3>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Total Seats:</dt>
                                    <dd class="font-semibold">{{ $flight->aircraft->total_seats }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Booked:</dt>
                                    <dd class="font-semibold">{{ $flight->booked_seats_count }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Load Factor:</dt>
                                    <dd class="font-semibold">{{ $flight->load_factor }}%</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Demand Score:</dt>
                                    <dd class="font-semibold">{{ $flight->demand_score }}/100</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fare Comparison Table -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-blue-900 mb-4">Compare Fare Classes</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-blue-300">
                                <th class="text-left py-3 px-4 font-semibold text-blue-900">Class</th>
                                <th class="text-right py-3 px-4 font-semibold text-blue-900">Price</th>
                                <th class="text-left py-3 px-4 font-semibold text-blue-900">Baggage</th>
                                <th class="text-left py-3 px-4 font-semibold text-blue-900">Refund</th>
                                <th class="text-left py-3 px-4 font-semibold text-blue-900">Changes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fareClasses as $fareClass)
                                @php
                                    $price = $prices[$fareClass->id] ?? null;
                                    $available = $availability[$fareClass->id] ?? 0;
                                    $rules = $fareRules[$fareClass->id] ?? null;
                                @endphp
                                <tr class="border-b border-blue-100 hover:bg-blue-100 transition">
                                    <td class="py-3 px-4">
                                        <p class="font-semibold text-gray-900">{{ $fareClass->name }}</p>
                                        <p class="text-xs text-gray-600">{{ $available }} seat(s) available</p>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        @if($price && $available > 0)
                                            <p class="font-bold text-lg text-indigo-600">₱{{ number_format($price, 2) }}</p>
                                        @else
                                            <p class="text-red-500 font-semibold">Sold Out</p>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-gray-700">
                                        {{ $rules ? $rules['baggage'] : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-700">
                                        {{ $rules ? $rules['refundable'] : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-700">
                                        {{ $rules ? $rules['change_fee'] : 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Fare Classes and Booking -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Select Fare Class</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($fareClasses as $fareClass)
                            @php
                                $price = $prices[$fareClass->id] ?? null;
                                $trend = $trends[$fareClass->id] ?? '→';
                                $available = $availability[$fareClass->id] ?? 0;
                            @endphp
                            
                            <div class="border rounded-lg p-6 hover:shadow-lg transition">
                                <div class="text-center">
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">{{ $fareClass->name }}</h4>
                                    <p class="text-sm text-gray-500 mb-4">{{ $fareClass->description }}</p>
                                    
                                    @if($price && $available > 0)
                                        <div class="mb-4">
                                            <div class="flex items-center justify-center space-x-2">
                                                <p class="text-3xl font-bold text-indigo-600">₱{{ number_format($price, 2) }}</p>
                                                <span class="text-xl">{{ $trend }}</span>
                                            </div>
                                            <p class="text-xs text-gray-400 mt-1">per passenger</p>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <p class="text-sm text-gray-600">
                                                {{ $available }} seat(s) available
                                            </p>
                                            
                                            @if($available < 10)
                                                <p class="text-xs text-red-600 font-semibold mt-1">
                                                    Only {{ $available }} left!
                                                </p>
                                            @endif
                                        </div>
                                        
                                        <!-- Booking Form -->
                                        <form action="{{ route('bookings.create') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="flight_id" value="{{ $flight->id }}">
                                        <input type="hidden" name="fare_class_id" value="{{ $fareClass->id }}">
                                        
                                        <div class="mb-3">
                                        <label for="seat_count_{{ $fareClass->id }}" class="block text-sm text-gray-600 mb-1">
                                        Number of Passengers
                                        </label>
                                        <select name="seat_count" 
                                            id="seat_count_{{ $fareClass->id }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @for($i = 1; $i <= min(9, $available); $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                        </select>
                                        </div>
                                        
                                        <button type="submit" 
                                            class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        Book Now
                                        </button>
                                        </form>
                                    @else
                                        <div class="mb-4">
                                            <p class="text-2xl font-bold text-red-500">SOLD OUT</p>
                                        </div>
                                        
                                        <button disabled class="w-full px-4 py-2 bg-gray-300 text-gray-500 rounded-md cursor-not-allowed">
                                            Unavailable
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Back Button -->
            <div class="mt-6">
                <a href="{{ route('flights.search') }}" class="text-indigo-600 hover:text-indigo-800">
                    ← Back to Search
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
