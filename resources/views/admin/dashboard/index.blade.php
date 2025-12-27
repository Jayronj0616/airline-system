<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Metric Cards (4-Column Grid) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Revenue -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">
                                    Total Revenue
                                </p>
                                <p class="mt-2 text-3xl font-semibold text-gray-900">
                                    ₱{{ number_format($metrics['total_revenue'], 2) }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    This Month
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <svg class="h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Bookings -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">
                                    Total Bookings
                                </p>
                                <p class="mt-2 text-3xl font-semibold text-gray-900">
                                    {{ number_format($metrics['total_bookings']) }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    This Month
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <svg class="h-12 w-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Average Load Factor -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">
                                    Avg Load Factor
                                </p>
                                <p class="mt-2 text-3xl font-semibold text-gray-900">
                                    {{ number_format($metrics['average_load_factor'], 2) }}%
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    @if($metrics['average_load_factor'] >= 85)
                                        <span class="text-green-600 font-medium">✓ Above Target</span>
                                    @elseif($metrics['average_load_factor'] >= 70)
                                        <span class="text-yellow-600 font-medium">~ Near Target</span>
                                    @else
                                        <span class="text-red-600 font-medium">✗ Below Target</span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <svg class="h-12 w-12 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Average Ticket Price -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">
                                    Avg Ticket Price
                                </p>
                                <p class="mt-2 text-3xl font-semibold text-gray-900">
                                    ₱{{ number_format($metrics['average_ticket_price'], 2) }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    This Month
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <svg class="h-12 w-12 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Chart 1: Revenue Over Time (Line Chart) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Revenue Over Time (Last 30 Days)</h3>
                        <canvas id="revenueChart" height="100"></canvas>
                    </div>
                </div>

                <!-- Chart 2: Bookings by Fare Class (Pie Chart) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Bookings by Fare Class</h3>
                        <canvas id="fareClassChart" height="100"></canvas>
                    </div>
                </div>

                <!-- Chart 3: Load Factor by Flight (Bar Chart) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Load Factor by Flight</h3>
                        <canvas id="loadFactorChart" height="100"></canvas>
                    </div>
                </div>

                <!-- Chart 4: Price vs Demand Correlation (Scatter Plot) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Price vs Demand Correlation</h3>
                        <canvas id="demandChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <!-- Demand Trends Section (Task 5) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- High Demand Flights -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-green-600">Top 5 High-Demand Flights</h3>
                        <div class="space-y-3">
                            @foreach($demandTrends['high_demand'] as $flight)
                                <div class="border-l-4 border-green-500 pl-4 py-2 bg-green-50">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $flight['flight_number'] }}</p>
                                            <p class="text-sm text-gray-600">{{ $flight['route'] }}</p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Demand: <span class="font-medium">{{ $flight['demand_score'] }}</span> | 
                                                Load Factor: <span class="font-medium">{{ $flight['load_factor'] }}%</span>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-gray-900">₱{{ number_format($flight['avg_price'], 2) }}</p>
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full 
                                                @if($flight['suggestion']['action'] === 'increase') bg-green-100 text-green-800
                                                @elseif($flight['suggestion']['action'] === 'decrease') bg-red-100 text-red-800
                                                @else bg-blue-100 text-blue-800
                                                @endif">
                                                @if($flight['suggestion']['action'] === 'increase')
                                                    ↑ {{ $flight['suggestion']['percentage'] }}%
                                                @elseif($flight['suggestion']['action'] === 'decrease')
                                                    ↓ {{ $flight['suggestion']['percentage'] }}%
                                                @else
                                                    Hold
                                                @endif
                                            </span>
                                            <p class="text-xs text-gray-500 mt-1">{{ $flight['suggestion']['reason'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Low Demand Flights -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-red-600">Top 5 Low-Demand Flights</h3>
                        <div class="space-y-3">
                            @foreach($demandTrends['low_demand'] as $flight)
                                <div class="border-l-4 border-red-500 pl-4 py-2 bg-red-50">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $flight['flight_number'] }}</p>
                                            <p class="text-sm text-gray-600">{{ $flight['route'] }}</p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Demand: <span class="font-medium">{{ $flight['demand_score'] }}</span> | 
                                                Load Factor: <span class="font-medium">{{ $flight['load_factor'] }}%</span>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-gray-900">₱{{ number_format($flight['avg_price'], 2) }}</p>
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full 
                                                @if($flight['suggestion']['action'] === 'increase') bg-green-100 text-green-800
                                                @elseif($flight['suggestion']['action'] === 'decrease') bg-red-100 text-red-800
                                                @else bg-blue-100 text-blue-800
                                                @endif">
                                                @if($flight['suggestion']['action'] === 'increase')
                                                    ↑ {{ $flight['suggestion']['percentage'] }}%
                                                @elseif($flight['suggestion']['action'] === 'decrease')
                                                    ↓ {{ $flight['suggestion']['percentage'] }}%
                                                @else
                                                    Hold
                                                @endif
                                            </span>
                                            <p class="text-xs text-gray-500 mt-1">{{ $flight['suggestion']['reason'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section (Task 7) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Filters</h3>
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Date From -->
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date" 
                                   id="date_from" 
                                   name="date_from" 
                                   value="{{ $filters['date_from'] ?? '' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- Date To -->
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                            <input type="date" 
                                   id="date_to" 
                                   name="date_to" 
                                   value="{{ $filters['date_to'] ?? '' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- Flight Filter -->
                        <div>
                            <label for="flight_id" class="block text-sm font-medium text-gray-700 mb-1">Flight</label>
                            <select id="flight_id" 
                                    name="flight_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Flights</option>
                                @foreach($allFlights as $flight)
                                    <option value="{{ $flight->id }}" 
                                            {{ ($filters['flight_id'] ?? '') == $flight->id ? 'selected' : '' }}>
                                        {{ $flight->flight_number }} ({{ $flight->origin }} → {{ $flight->destination }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fare Class Filter -->
                        <div>
                            <label for="fare_class_id" class="block text-sm font-medium text-gray-700 mb-1">Fare Class</label>
                            <select id="fare_class_id" 
                                    name="fare_class_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Classes</option>
                                @foreach($fareClasses as $fareClass)
                                    <option value="{{ $fareClass->id }}" 
                                            {{ ($filters['fare_class_id'] ?? '') == $fareClass->id ? 'selected' : '' }}>
                                        {{ $fareClass->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Submit and Reset Buttons -->
                        <div class="md:col-span-4 flex justify-end space-x-3">
                            <a href="{{ route('admin.dashboard') }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Reset
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Price History Visualization (Task 6) -->
            @if($priceHistoryData)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="text-lg font-semibold">Price History - {{ $priceHistoryData['flight']->flight_number }}</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ $priceHistoryData['flight']->origin }} → {{ $priceHistoryData['flight']->destination }} | 
                                    Departure: {{ $priceHistoryData['flight']->departure_time->format('M d, Y H:i') }}
                                </p>
                            </div>
                            <div class="text-sm text-gray-500">
                                <span class="inline-flex items-center">
                                    <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span> Economy
                                </span>
                                <span class="inline-flex items-center ml-4">
                                    <span class="w-3 h-3 bg-purple-500 rounded-full mr-2"></span> Business
                                </span>
                                <span class="inline-flex items-center ml-4">
                                    <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span> First
                                </span>
                                <span class="inline-flex items-center ml-4">
                                    <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span> Booking Event
                                </span>
                            </div>
                        </div>
                        <canvas id="priceHistoryChart" height="80"></canvas>
                    </div>
                </div>
            @endif

            <!-- Flight Performance Table (Task 4) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Flight Performance</h3>
                        <a href="{{ route('admin.dashboard.export-csv') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export CSV
                        </a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table id="performanceTable" class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(0)">
                                        Flight ▼
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(1)">
                                        Route ▼
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(2)">
                                        Load Factor (%) ▼
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(3)">
                                        Revenue (₱) ▼
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(4)">
                                        Avg Ticket Price (₱) ▼
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(5)">
                                        Seats Sold ▼
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($flightPerformance as $flight)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $flight['flight_number'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $flight['route'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($flight['load_factor'] >= 85) bg-green-100 text-green-800
                                                @elseif($flight['load_factor'] >= 70) bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                {{ $flight['load_factor'] }}%
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ₱{{ number_format($flight['revenue'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ₱{{ number_format($flight['avg_ticket_price'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $flight['seats_sold'] }} / {{ $flight['total_seats'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <script>
        // Chart 1: Revenue Over Time (Line Chart)
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: @json($chartData['revenue_over_time']['labels']),
                datasets: [{
                    label: 'Daily Revenue (₱)',
                    data: @json($chartData['revenue_over_time']['data']),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Chart 2: Bookings by Fare Class (Pie Chart)
        const fareClassCtx = document.getElementById('fareClassChart').getContext('2d');
        new Chart(fareClassCtx, {
            type: 'pie',
            data: {
                labels: @json($chartData['bookings_by_fare_class']['labels']),
                datasets: [{
                    data: @json($chartData['bookings_by_fare_class']['data']),
                    backgroundColor: [
                        'rgb(59, 130, 246)',   // Blue
                        'rgb(168, 85, 247)',   // Purple
                        'rgb(234, 179, 8)',    // Yellow
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Chart 3: Load Factor by Flight (Bar Chart)
        const loadFactorCtx = document.getElementById('loadFactorChart').getContext('2d');
        new Chart(loadFactorCtx, {
            type: 'bar',
            data: {
                labels: @json($chartData['load_factor_by_flight']['labels']),
                datasets: [{
                    label: 'Load Factor (%)',
                    data: @json($chartData['load_factor_by_flight']['data']),
                    backgroundColor: @json($chartData['load_factor_by_flight']['colors'])
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });

        // Chart 4: Price vs Demand Correlation (Scatter Plot)
        const demandCtx = document.getElementById('demandChart').getContext('2d');
        new Chart(demandCtx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Flights',
                    data: @json($chartData['price_vs_demand']),
                    backgroundColor: 'rgb(99, 102, 241)',
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const point = context.raw;
                                return [
                                    'Flight: ' + point.label,
                                    'Demand: ' + point.x,
                                    'Avg Price: ₱' + point.y.toLocaleString()
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Demand Score'
                        },
                        min: 0,
                        max: 100
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Average Price (₱)'
                        },
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Chart 5: Price History Visualization (Task 6)
        @if($priceHistoryData)
            const priceHistoryCtx = document.getElementById('priceHistoryChart').getContext('2d');
            
            // Prepare datasets for price history (by fare class)
            const priceDatasets = @json($priceHistoryData['datasets']);
            
            // Prepare booking events as a separate dataset with point markers
            const bookingEvents = @json($priceHistoryData['bookingEvents']);
            const bookingEventDataset = {
                label: 'Booking Events',
                data: bookingEvents.map(event => ({
                    x: event.x,
                    y: event.price / event.seats // Average price per seat for the booking
                })),
                backgroundColor: 'rgb(239, 68, 68)',
                borderColor: 'rgb(239, 68, 68)',
                pointRadius: 8,
                pointStyle: 'star',
                showLine: false,
                type: 'scatter'
            };
            
            new Chart(priceHistoryCtx, {
                type: 'line',
                data: {
                    datasets: [...priceDatasets, bookingEventDataset]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (context.dataset.label === 'Booking Events') {
                                        const event = bookingEvents[context.dataIndex];
                                        return [
                                            'Booking Event',
                                            'Seats: ' + event.seats,
                                            'Total Price: ₱' + event.price.toLocaleString()
                                        ];
                                    }
                                    return context.dataset.label + ': ₱' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'day',
                                displayFormats: {
                                    day: 'MMM d'
                                }
                            },
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Price (₱)'
                            },
                            beginAtZero: false,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        @endif

        // Table sorting functionality
        let sortDirection = {};
        
        function sortTable(columnIndex) {
            const table = document.getElementById('performanceTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            // Toggle sort direction
            sortDirection[columnIndex] = !sortDirection[columnIndex];
            const ascending = sortDirection[columnIndex];
            
            rows.sort((a, b) => {
                let aValue = a.cells[columnIndex].textContent.trim();
                let bValue = b.cells[columnIndex].textContent.trim();
                
                // Handle numeric values (remove currency symbols and commas)
                if (columnIndex >= 2) {
                    aValue = parseFloat(aValue.replace(/[₱,%]/g, '').replace(/,/g, ''));
                    bValue = parseFloat(bValue.replace(/[₱,%]/g, '').replace(/,/g, ''));
                }
                
                if (aValue < bValue) return ascending ? -1 : 1;
                if (aValue > bValue) return ascending ? 1 : -1;
                return 0;
            });
            
            // Clear and repopulate tbody
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        }
    </script>
    @endpush
</x-app-layout>
