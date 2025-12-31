<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Flight Details') }} - {{ $flight->flight_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Back Button -->
            <div class="mb-6">
                <a href="{{ route('flight-status.index') }}" class="text-blue-600 hover:text-blue-800">
                    ← Back to Search
                </a>
            </div>

            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-3xl font-bold mb-2">{{ $flight->flight_number }}</h1>
                            <p class="text-blue-100">{{ $flight->aircraft->model }} • {{ $flight->aircraft->registration }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-6 py-3 rounded-full text-lg font-bold bg-white
                                {{ $flight->status_info['color'] === 'blue' ? 'text-blue-600' : '' }}
                                {{ $flight->status_info['color'] === 'green' ? 'text-green-600' : '' }}
                                {{ $flight->status_info['color'] === 'orange' ? 'text-orange-600' : '' }}
                                {{ $flight->status_info['color'] === 'purple' ? 'text-purple-600' : '' }}
                                {{ $flight->status_info['color'] === 'gray' ? 'text-gray-600' : '' }}">
                                {{ $flight->status_info['icon'] }} {{ $flight->status_info['status'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Flight Route -->
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Departure -->
                        <div class="text-center md:text-left">
                            <p class="text-sm text-gray-500 mb-2">DEPARTURE</p>
                            <h2 class="text-4xl font-bold text-gray-900 mb-2">{{ $flight->origin }}</h2>
                            <p class="text-2xl font-semibold text-gray-700 mb-1">
                                {{ \Carbon\Carbon::parse($flight->departure_time)->format('H:i') }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($flight->departure_time)->format('l, M d, Y') }}
                            </p>
                            <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                                <p class="text-xs text-gray-500">Boarding starts at</p>
                                <p class="font-semibold text-blue-800">
                                    {{ \Carbon\Carbon::parse($flight->departure_time)->subMinutes(30)->format('H:i') }}
                                </p>
                            </div>
                        </div>

                        <!-- Flight Path -->
                        <div class="flex flex-col items-center justify-center">
                            <div class="text-5xl text-gray-300 mb-4">✈</div>
                            @if(isset($flight->status_info['progress']))
                                <div class="w-full bg-gray-200 rounded-full h-3 mb-3">
                                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-3 rounded-full transition-all duration-500" 
                                         style="width: {{ $flight->status_info['progress'] }}%"></div>
                                </div>
                                <p class="text-sm font-semibold text-purple-600">{{ $flight->status_info['progress'] }}% Complete</p>
                            @endif
                            <div class="mt-4 text-center">
                                <p class="text-sm text-gray-500">Flight Duration</p>
                                <p class="text-xl font-bold text-gray-900">
                                    {{ \Carbon\Carbon::parse($flight->departure_time)->diffInHours(\Carbon\Carbon::parse($flight->arrival_time)) }}h 
                                    {{ \Carbon\Carbon::parse($flight->departure_time)->diffInMinutes(\Carbon\Carbon::parse($flight->arrival_time)) % 60 }}m
                                </p>
                            </div>
                        </div>

                        <!-- Arrival -->
                        <div class="text-center md:text-right">
                            <p class="text-sm text-gray-500 mb-2">ARRIVAL</p>
                            <h2 class="text-4xl font-bold text-gray-900 mb-2">{{ $flight->destination }}</h2>
                            <p class="text-2xl font-semibold text-gray-700 mb-1">
                                {{ \Carbon\Carbon::parse($flight->arrival_time)->format('H:i') }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($flight->arrival_time)->format('l, M d, Y') }}
                            </p>
                            <div class="mt-4 p-3 bg-green-50 rounded-lg">
                                <p class="text-xs text-gray-500">Expected arrival</p>
                                <p class="font-semibold text-green-800">On time</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Message -->
                    <div class="mt-8 p-6 bg-gradient-to-r 
                        {{ $flight->status_info['color'] === 'blue' ? 'from-blue-50 to-blue-100' : '' }}
                        {{ $flight->status_info['color'] === 'green' ? 'from-green-50 to-green-100' : '' }}
                        {{ $flight->status_info['color'] === 'orange' ? 'from-orange-50 to-orange-100' : '' }}
                        {{ $flight->status_info['color'] === 'purple' ? 'from-purple-50 to-purple-100' : '' }}
                        {{ $flight->status_info['color'] === 'gray' ? 'from-gray-50 to-gray-100' : '' }}
                        rounded-lg">
                        <p class="text-lg font-semibold 
                            {{ $flight->status_info['color'] === 'blue' ? 'text-blue-900' : '' }}
                            {{ $flight->status_info['color'] === 'green' ? 'text-green-900' : '' }}
                            {{ $flight->status_info['color'] === 'orange' ? 'text-orange-900' : '' }}
                            {{ $flight->status_info['color'] === 'purple' ? 'text-purple-900' : '' }}
                            {{ $flight->status_info['color'] === 'gray' ? 'text-gray-900' : '' }}">
                            {{ $flight->status_info['message'] }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Flight Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Aircraft Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-4">Aircraft Details</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Model:</span>
                            <span class="font-semibold">{{ $flight->aircraft->model }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Registration:</span>
                            <span class="font-semibold">{{ $flight->aircraft->registration }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Seats:</span>
                            <span class="font-semibold">{{ $flight->aircraft->total_seats }}</span>
                        </div>
                    </div>
                </div>

                <!-- Booking Statistics -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-4">Booking Statistics</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Bookings:</span>
                            <span class="font-semibold">{{ $flight->bookings->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Passengers:</span>
                            <span class="font-semibold">{{ $flight->bookings->sum('seat_count') }}</span>
                        </div>
                        @php
                            $loadFactor = ($flight->bookings->sum('seat_count') / $flight->aircraft->total_seats) * 100;
                        @endphp
                        <div class="flex justify-between">
                            <span class="text-gray-600">Load Factor:</span>
                            <span class="font-semibold {{ $loadFactor >= 80 ? 'text-green-600' : 'text-orange-600' }}">
                                {{ number_format($loadFactor, 1) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-4">Important Information</h3>
                <div class="space-y-4 text-sm text-gray-700">
                    <div class="flex items-start gap-3">
                        <span class="text-blue-600">ℹ️</span>
                        <p>Please arrive at the airport at least 3 hours before departure for international flights.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-green-600">✓</span>
                        <p>Online check-in opens 24 hours before departure and closes 90 minutes before departure.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-orange-600">⚠</span>
                        <p>Gates close 15 minutes before scheduled departure. Please ensure you are at the gate on time.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
