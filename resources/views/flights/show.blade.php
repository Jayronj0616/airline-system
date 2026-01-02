<x-public-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Flight Details') }} - {{ $flight->flight_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Flight Info Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4 dark:text-white">Flight Information</h3>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600 dark:text-gray-400">Flight Number:</dt>
                                    <dd class="font-semibold dark:text-gray-200">{{ $flight->flight_number }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600 dark:text-gray-400">Aircraft:</dt>
                                    <dd class="font-semibold dark:text-gray-200">{{ $flight->aircraft->model }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600 dark:text-gray-400">Route:</dt>
                                    <dd class="font-semibold dark:text-gray-200">{{ $flight->origin }} → {{ $flight->destination }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600 dark:text-gray-400">Departure:</dt>
                                    <dd class="font-semibold dark:text-gray-200">{{ $flight->departure_time->format('M d, Y h:i A') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600 dark:text-gray-400">Arrival:</dt>
                                    <dd class="font-semibold dark:text-gray-200">{{ $flight->arrival_time->format('M d, Y h:i A') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600 dark:text-gray-400">Status:</dt>
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
                            <h3 class="text-lg font-semibold mb-4 dark:text-white">Capacity</h3>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600 dark:text-gray-400">Total Seats:</dt>
                                    <dd class="font-semibold dark:text-gray-200">{{ $flight->aircraft->total_seats }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600 dark:text-gray-400">Booked:</dt>
                                    <dd class="font-semibold dark:text-gray-200">{{ $flight->booked_seats_count }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600 dark:text-gray-400">Load Factor:</dt>
                                    <dd class="font-semibold dark:text-gray-200">{{ $flight->load_factor }}%</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600 dark:text-gray-400">Demand Score:</dt>
                                    <dd class="font-semibold dark:text-gray-200">{{ $flight->demand_score }}/100</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fare Comparison Table -->
            <div class="bg-blue-50 dark:bg-gray-800 border border-blue-200 dark:border-gray-700 rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-blue-900 dark:text-blue-400 mb-4">Compare Fare Classes</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-blue-300 dark:border-gray-600">
                                <th class="text-left py-3 px-4 font-semibold text-blue-900 dark:text-blue-400">Class</th>
                                <th class="text-right py-3 px-4 font-semibold text-blue-900 dark:text-blue-400">Price</th>
                                <th class="text-left py-3 px-4 font-semibold text-blue-900 dark:text-blue-400">Baggage</th>
                                <th class="text-left py-3 px-4 font-semibold text-blue-900 dark:text-blue-400">Refund</th>
                                <th class="text-left py-3 px-4 font-semibold text-blue-900 dark:text-blue-400">Changes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fareClasses as $fareClass)
                                @php
                                    $price = $prices[$fareClass->id] ?? null;
                                    $available = $availability[$fareClass->id] ?? 0;
                                    $rules = $fareRules[$fareClass->id] ?? null;
                                @endphp
                                <tr class="border-b border-blue-100 dark:border-gray-700 hover:bg-blue-100 dark:hover:bg-gray-700 transition">
                                    <td class="py-3 px-4">
                                        <p class="font-semibold text-gray-900 dark:text-gray-200">{{ $fareClass->name }}</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ $available }} seat(s) available</p>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        @if($price && $available > 0)
                                            <p class="font-bold text-lg text-indigo-600 dark:text-indigo-400">₱{{ number_format($price, 2) }}</p>
                                        @else
                                            <p class="text-red-500 dark:text-red-400 font-semibold">Sold Out</p>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                        {{ $rules ? $rules['baggage'] : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                        {{ $rules ? $rules['refundable'] : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                        {{ $rules ? $rules['change_fee'] : 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Fare Classes and Booking -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 dark:text-white">Select Fare Class</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($fareClasses as $fareClass)
                            @php
                                $price = $prices[$fareClass->id] ?? null;
                                $trend = $trends[$fareClass->id] ?? '→';
                                $available = $availability[$fareClass->id] ?? 0;
                            @endphp
                            
                            <div class="border dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition">
                                <div class="text-center">
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $fareClass->name }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $fareClass->description }}</p>
                                    
                                    @if($price && $available > 0)
                                        <div class="mb-4">
                                            <div class="flex items-center justify-center space-x-2">
                                                <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">₱{{ number_format($price, 2) }}</p>
                                                <span class="text-xl dark:text-gray-300">{{ $trend }}</span>
                                            </div>
                                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">per passenger</p>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $available }} seat(s) available
                                            </p>
                                            
                                            @if($available < 10)
                                                <p class="text-xs text-red-600 dark:text-red-400 font-semibold mt-1">
                                                    Only {{ $available }} left!
                                                </p>
                                            @endif
                                        </div>
                                        
                                        <!-- Booking Form -->
                                        <form action="{{ route('booking.review-fare') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="flight_id" value="{{ $flight->id }}">
                                            <input type="hidden" name="fare_class_id" value="{{ $fareClass->id }}">
                                            
                                            <div class="mb-3">
                                                <label for="passenger_count_{{ $fareClass->id }}" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                    Number of Passengers
                                                </label>
                                                <select name="passenger_count" 
                                                    id="passenger_count_{{ $fareClass->id }}"
                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    @for($i = 1; $i <= min(9, $available); $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            
                                            <button type="submit" 
                                                class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                Select Fare
                                            </button>
                                        </form>
                                    @else
                                        <div class="mb-4">
                                            <p class="text-2xl font-bold text-red-500 dark:text-red-400">SOLD OUT</p>
                                        </div>
                                        
                                        <button disabled class="w-full px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-md cursor-not-allowed">
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
                <a href="{{ route('flights.search') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                    ← Back to Search
                </a>
            </div>
        </div>
    </div>
</x-public-layout>
