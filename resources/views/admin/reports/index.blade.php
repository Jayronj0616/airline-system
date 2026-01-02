<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Header & Filters -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Reports & Monitoring</h2>
                    </div>

                    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date From</label>
                            <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date To</label>
                            <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Apply Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Metrics Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Bookings</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ number_format($metrics['total_bookings']) }}</p>
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ number_format($metrics['cancelled_bookings']) }} cancelled</p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Revenue (Paid)</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">₱{{ number_format($metrics['total_revenue'], 2) }}</p>
                    <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">₱{{ number_format($metrics['total_revenue_unpaid'], 2) }} unpaid</p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg Load Factor</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $metrics['avg_load_factor'] }}%</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Seat occupancy rate</p>
                </div>
            </div>

            <!-- Add-on Revenue -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Add-on Sales</h3>
                        <span class="text-2xl font-bold text-green-600">₱{{ number_format($metrics['addon_revenue'], 2) }}</span>
                    </div>
                    
                    @if($topAddons->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Add-on</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Quantity Sold</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($topAddons as $addon)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ $addon['name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ number_format($addon['quantity']) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">₱{{ number_format($addon['revenue'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">No add-on sales in this period</p>
                    @endif
                </div>
            </div>

            <!-- Bookings Per Flight -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Bookings Per Flight</h3>
                    
                    @if($bookingsPerFlight->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Flight</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Route</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Departure</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bookings</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Seats</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Load Factor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($bookingsPerFlight as $flight)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-gray-100">{{ $flight['flight_number'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ $flight['route'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $flight['departure_time']->format('M d, H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ $flight['bookings'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ $flight['seats_sold'] }}/{{ $flight['total_seats'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded {{ $flight['load_factor'] >= 85 ? 'bg-green-100 text-green-800' : ($flight['load_factor'] >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $flight['load_factor'] }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">₱{{ number_format($flight['revenue'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">No flight data in this period</p>
                    @endif
                </div>
            </div>

            <!-- Revenue by Fare Class -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Revenue by Fare Class</h3>
                    
                    @if($revenueByFareClass->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fare Class</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bookings</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($revenueByFareClass as $fareClass)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-gray-100">{{ $fareClass['fare_class'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ number_format($fareClass['bookings']) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">₱{{ number_format($fareClass['revenue'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">No revenue data in this period</p>
                    @endif
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Export Reports</h3>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('admin.reports.export-bookings', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" 
                           class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded">
                            📊 Export Bookings (CSV)
                        </a>
                        <a href="{{ route('admin.reports.export-revenue', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" 
                           class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
                            💰 Export Revenue (CSV)
                        </a>
                        <a href="{{ route('admin.reports.export-addons', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" 
                           class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-2 rounded">
                            🛍️ Export Add-ons (CSV)
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-admin-layout>
