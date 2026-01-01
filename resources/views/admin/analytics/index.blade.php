<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📊 Advanced Analytics Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Booking Heatmap -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">🔥 Booking Heatmap - Peak Booking Hours</h3>
                    <p class="text-sm text-gray-600 mb-4">Last 30 days | Darker = More bookings</p>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-center text-xs">
                            <thead>
                                <tr>
                                    <th class="p-2 border">Day / Hour</th>
                                    @foreach($analytics['booking_heatmap']['hours'] as $hour)
                                        <th class="p-2 border">{{ $hour }}h</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $max = $analytics['booking_heatmap']['max_bookings'];
                                @endphp
                                @foreach($analytics['booking_heatmap']['days'] as $dayIndex => $dayName)
                                    <tr>
                                        <td class="p-2 border font-semibold text-left">{{ $dayName }}</td>
                                        @foreach($analytics['booking_heatmap']['hours'] as $hour)
                                            @php
                                                $count = $analytics['booking_heatmap']['matrix'][$dayIndex + 1][$hour] ?? 0;
                                                $intensity = $max > 0 ? ($count / $max) : 0;
                                                $opacity = 0.2 + ($intensity * 0.8);
                                            @endphp
                                            <td class="p-3 border relative group cursor-pointer"
                                                style="background-color: rgba(59, 130, 246, {{ $opacity }})">
                                                <span class="font-semibold">{{ $count }}</span>
                                                <div class="hidden group-hover:block absolute z-10 bg-gray-900 text-white text-xs rounded py-1 px-2 -mt-8 whitespace-nowrap">
                                                    {{ $dayName }} {{ $hour }}:00 - {{ $count }} bookings
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 flex items-center gap-4 text-sm">
                        <span class="font-semibold">Insight:</span>
                        <span class="text-gray-600">Peak hours for bookings help optimize staff scheduling and marketing campaigns</span>
                    </div>
                </div>
            </div>

            <!-- Revenue Forecast -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📈 Revenue Forecast - Next 30 Days</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Avg Daily Revenue</p>
                            <p class="text-2xl font-bold text-blue-600">${{ number_format($analytics['revenue_forecast']['avg_daily'], 2) }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Projected Monthly</p>
                            <p class="text-2xl font-bold text-green-600">${{ number_format($analytics['revenue_forecast']['projected_monthly'], 2) }}</p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Growth Trend</p>
                            <p class="text-2xl font-bold text-purple-600">+5%</p>
                        </div>
                    </div>
                    
                    <canvas id="revenueForecastChart" height="80"></canvas>
                </div>
            </div>

            <!-- Top Performing Routes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">🏆 Top 10 Performing Routes</h3>
                    <p class="text-sm text-gray-600 mb-4">Last 30 days | Sorted by revenue</p>
                    
                    <canvas id="topRoutesChart" height="100"></canvas>
                    
                    <div class="mt-6 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="p-3 text-left">Route</th>
                                    <th class="p-3 text-right">Revenue</th>
                                    <th class="p-3 text-right">Bookings</th>
                                    <th class="p-3 text-right">Passengers</th>
                                    <th class="p-3 text-right">Avg Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analytics['top_routes']['routes'] as $route)
                                    <tr class="border-t">
                                        <td class="p-3 font-semibold">{{ $route->route }}</td>
                                        <td class="p-3 text-right text-green-600 font-bold">${{ number_format($route->total_revenue, 2) }}</td>
                                        <td class="p-3 text-right">{{ $route->total_bookings }}</td>
                                        <td class="p-3 text-right">{{ $route->total_passengers }}</td>
                                        <td class="p-3 text-right">${{ number_format($route->avg_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Occupancy Trends -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📊 Occupancy Rate Trends</h3>
                    
                    <div class="mb-4 bg-blue-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Average Occupancy Rate (Last 30 Days)</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $analytics['occupancy_trends']['avg_occupancy'] }}%</p>
                    </div>
                    
                    <canvas id="occupancyTrendsChart" height="80"></canvas>
                </div>
            </div>

            <!-- Booking Window Analysis -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">⏰ Booking Window Analysis</h3>
                    <p class="text-sm text-gray-600 mb-4">How far in advance customers book flights</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <canvas id="bookingWindowChart"></canvas>
                        </div>
                        <div>
                            <div class="space-y-4">
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <p class="text-sm text-gray-600">Total Bookings Analyzed</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ $analytics['booking_window_analysis']['total_bookings'] }}</p>
                                </div>
                                <div class="bg-green-50 rounded-lg p-4">
                                    <p class="text-sm text-gray-600">Average Days in Advance</p>
                                    <p class="text-2xl font-bold text-green-600">{{ $analytics['booking_window_analysis']['avg_days_advance'] }} days</p>
                                </div>
                                
                                <div class="space-y-2">
                                    @foreach($analytics['booking_window_analysis']['windows'] as $window => $count)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                            <span class="font-semibold">{{ $window }}</span>
                                            <div class="flex items-center gap-2">
                                                <span class="text-gray-600">{{ $count }} bookings</span>
                                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-semibold">
                                                    {{ $analytics['booking_window_analysis']['percentages'][$window] }}%
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <p class="font-semibold text-yellow-800">💡 Business Insight:</p>
                        <p class="text-sm text-yellow-700 mt-1">
                            Most customers book {{ $analytics['booking_window_analysis']['avg_days_advance'] }} days in advance. 
                            Optimize pricing and marketing campaigns around this window for maximum conversions.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Revenue Forecast Chart
        new Chart(document.getElementById('revenueForecastChart'), {
            type: 'line',
            data: {
                labels: [
                    ...{!! json_encode(array_column($analytics['revenue_forecast']['historical'], 'date')) !!},
                    ...{!! json_encode(array_column($analytics['revenue_forecast']['forecast'], 'date')) !!}
                ],
                datasets: [
                    {
                        label: 'Historical Revenue',
                        data: {!! json_encode(array_column($analytics['revenue_forecast']['historical'], 'revenue')) !!},
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Forecast Revenue',
                        data: Array({!! count($analytics['revenue_forecast']['historical']) !!}).fill(null).concat(
                            {!! json_encode(array_column($analytics['revenue_forecast']['forecast'], 'revenue')) !!}
                        ),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderDash: [5, 5],
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: { display: false },
                    legend: { position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Top Routes Chart
        new Chart(document.getElementById('topRoutesChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($analytics['top_routes']['labels']) !!},
                datasets: [{
                    label: 'Revenue ($)',
                    data: {!! json_encode($analytics['top_routes']['revenues']) !!},
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    title: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Occupancy Trends Chart
        new Chart(document.getElementById('occupancyTrendsChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($analytics['occupancy_trends']['labels']) !!},
                datasets: [{
                    label: 'Occupancy Rate (%)',
                    data: {!! json_encode($analytics['occupancy_trends']['occupancy_rates']) !!},
                    borderColor: 'rgb(168, 85, 247)',
                    backgroundColor: 'rgba(168, 85, 247, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
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

        // Booking Window Chart
        new Chart(document.getElementById('bookingWindowChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($analytics['booking_window_analysis']['labels']) !!},
                datasets: [{
                    data: {!! json_encode($analytics['booking_window_analysis']['data']) !!},
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(234, 179, 8, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(168, 85, 247, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
    @endpush
</x-admin-layout>
