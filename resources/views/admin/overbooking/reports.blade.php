<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Overbooking Reports & Analytics') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.overbooking.index') }}" 
                   class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    Back to Management
                </a>
                <form method="GET" action="{{ route('admin.overbooking.reports.export') }}" class="inline">
                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                    <input type="hidden" name="end_date" value="{{ $endDate }}">
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        📥 Export CSV
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Date Range Filter -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📅 Date Range</h3>
                    <form method="GET" action="{{ route('admin.overbooking.reports') }}" class="flex items-end space-x-4">
                        <div class="flex-1">
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Start Date
                            </label>
                            <input type="date" 
                                   name="start_date" 
                                   id="start_date" 
                                   value="{{ $startDate }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="flex-1">
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                End Date
                            </label>
                            <input type="date" 
                                   name="end_date" 
                                   id="end_date" 
                                   value="{{ $endDate }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <button type="submit" 
                                class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Apply Filter
                        </button>
                    </form>
                </div>
            </div>

            <!-- Key Performance Indicators -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Average Load Factor</div>
                    <div class="text-3xl font-bold {{ $stats['average_load_factor'] >= 90 ? 'text-green-600' : 'text-gray-900' }}">
                        {{ $stats['average_load_factor'] }}%
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        Goal: >90%
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Flights Overbooked</div>
                    <div class="text-3xl font-bold text-indigo-600">
                        {{ $stats['overbooked_flights'] }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $stats['overbooked_percentage'] }}% of total flights
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">High Load Factor Flights</div>
                    <div class="text-3xl font-bold text-green-600">
                        {{ $stats['high_load_factor_count'] }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $stats['high_load_factor_percentage'] }}% with ≥90% load
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Total Capacity Utilization</div>
                    <div class="text-3xl font-bold text-gray-900">
                        {{ number_format($stats['total_confirmed_bookings']) }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        of {{ number_format($stats['total_physical_capacity']) }} seats
                    </div>
                </div>
            </div>

            <!-- Revenue Impact Analysis -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">💰 Revenue Impact Analysis</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                        <div>
                            <div class="text-sm text-gray-500 mb-2">Revenue Gained</div>
                            <div class="text-2xl font-bold text-green-600">
                                ₱{{ number_format($revenueImpact['revenue_gained'], 2) }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                From {{ $revenueImpact['additional_bookings'] }} additional bookings
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 mb-2">Compensation Paid</div>
                            <div class="text-2xl font-bold text-red-600">
                                ₱{{ number_format($revenueImpact['compensation_paid'], 2) }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                For denied boardings
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 mb-2">Net Revenue</div>
                            <div class="text-2xl font-bold {{ $revenueImpact['net_revenue'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ₱{{ number_format($revenueImpact['net_revenue'], 2) }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Gained - Compensation
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 mb-2">ROI</div>
                            <div class="text-2xl font-bold {{ $revenueImpact['roi'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $revenueImpact['roi'] }}%
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Return on Investment
                            </div>
                        </div>

                        <div class="flex items-center justify-center">
                            @if($revenueImpact['roi'] >= 50)
                                <div class="text-center">
                                    <div class="text-4xl mb-2">🎯</div>
                                    <div class="text-sm font-semibold text-green-600">Excellent ROI!</div>
                                </div>
                            @elseif($revenueImpact['roi'] >= 0)
                                <div class="text-center">
                                    <div class="text-4xl mb-2">✅</div>
                                    <div class="text-sm font-semibold text-green-600">Profitable</div>
                                </div>
                            @else
                                <div class="text-center">
                                    <div class="text-4xl mb-2">⚠️</div>
                                    <div class="text-sm font-semibold text-red-600">Review Strategy</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 p-4 {{ $revenueImpact['roi'] >= 0 ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200' }} border rounded">
                        <p class="text-sm {{ $revenueImpact['roi'] >= 0 ? 'text-green-800' : 'text-yellow-800' }}">
                            @if($revenueImpact['roi'] >= 50)
                                <strong>💡 Analysis:</strong> Overbooking strategy is highly effective! Continue monitoring and maintain current approach.
                            @elseif($revenueImpact['roi'] >= 0)
                                <strong>💡 Analysis:</strong> Overbooking is profitable but compensation costs are reducing gains. Consider optimizing percentages.
                            @else
                                <strong>⚠️ Analysis:</strong> Compensation costs exceed overbooking revenue. Review overbooking percentages or improve no-show predictions.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Denied Boarding Statistics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">🚫 Denied Boarding Statistics</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <div class="text-sm text-gray-500 mb-2">Total Denied Boardings</div>
                            <div class="text-3xl font-bold text-red-600">
                                {{ $deniedBoardingStats['total_denied'] }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 mb-2">Average Compensation</div>
                            <div class="text-3xl font-bold text-gray-900">
                                ₱{{ number_format($deniedBoardingStats['avg_compensation'], 2) }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 mb-2">Total Compensation</div>
                            <div class="text-3xl font-bold text-gray-900">
                                ₱{{ number_format($deniedBoardingStats['total_compensation'], 2) }}
                            </div>
                        </div>
                    </div>

                    @if($deniedBoardingStats['total_denied'] > 0)
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- By Resolution Type -->
                            <div>
                                <h4 class="font-semibold mb-3">Resolution Type Breakdown</h4>
                                <div class="space-y-2">
                                    @foreach($deniedBoardingStats['by_resolution'] as $type => $count)
                                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                            <span class="text-sm text-gray-700">{{ ucfirst($type) }}</span>
                                            <span class="font-semibold text-gray-900">{{ $count }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- By Fare Class -->
                            <div>
                                <h4 class="font-semibold mb-3">Fare Class Breakdown</h4>
                                <div class="space-y-2">
                                    @foreach($deniedBoardingStats['by_fare_class'] as $fareClassId => $count)
                                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                            <span class="text-sm text-gray-700">Fare Class #{{ $fareClassId }}</span>
                                            <span class="font-semibold text-gray-900">{{ $count }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 text-center py-8">
                            <div class="text-6xl mb-4">✅</div>
                            <h4 class="text-xl font-semibold text-gray-900 mb-2">Perfect Record!</h4>
                            <p class="text-gray-600">No denied boardings in this period.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Top Performing Flights -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">🏆 Top 10 Performing Flights (by Load Factor)</h3>
                    
                    @if($topPerformers->isEmpty())
                        <p class="text-center text-gray-500 py-8">No flight data available for this period.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flight</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departure</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Load Factor</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overbooked</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($topPerformers as $index => $flight)
                                        @php
                                            $stats = $flight->overbooking_stats;
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($index == 0)
                                                    <span class="text-2xl">🥇</span>
                                                @elseif($index == 1)
                                                    <span class="text-2xl">🥈</span>
                                                @elseif($index == 2)
                                                    <span class="text-2xl">🥉</span>
                                                @else
                                                    <span class="text-gray-600 font-semibold">#{{ $index + 1 }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $flight->flight_number }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $flight->origin }} → {{ $flight->destination }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $flight->departure_time->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {{ $stats['confirmed_bookings'] }} / {{ $stats['physical_capacity'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $stats['load_factor'] >= 100 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ $stats['load_factor'] }}%
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($stats['overbooked_count'] > 0)
                                                    <span class="text-red-600 font-semibold">+{{ $stats['overbooked_count'] }}</span>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Insights & Recommendations -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">💡 Insights & Recommendations</h3>
                    
                    <div class="space-y-3">
                        @if($stats['average_load_factor'] >= 90)
                            <div class="p-3 bg-green-50 border-l-4 border-green-500">
                                <p class="text-sm text-green-800">
                                    <strong>✅ Excellent Performance:</strong> Average load factor of {{ $stats['average_load_factor'] }}% exceeds the 90% target. Overbooking strategy is effective.
                                </p>
                            </div>
                        @else
                            <div class="p-3 bg-yellow-50 border-l-4 border-yellow-500">
                                <p class="text-sm text-yellow-800">
                                    <strong>⚠️ Room for Improvement:</strong> Average load factor is {{ $stats['average_load_factor'] }}%. Consider increasing overbooking percentages on routes with high no-show rates.
                                </p>
                            </div>
                        @endif

                        @if($deniedBoardingStats['total_denied'] > 0)
                            <div class="p-3 bg-red-50 border-l-4 border-red-500">
                                <p class="text-sm text-red-800">
                                    <strong>🚨 Denied Boardings Detected:</strong> {{ $deniedBoardingStats['total_denied'] }} passengers were denied boarding. Review overbooking percentages and improve no-show predictions.
                                </p>
                            </div>
                        @endif

                        @if($revenueImpact['roi'] < 0)
                            <div class="p-3 bg-red-50 border-l-4 border-red-500">
                                <p class="text-sm text-red-800">
                                    <strong>📉 Negative ROI:</strong> Compensation costs exceed overbooking revenue. Reduce overbooking percentages or improve targeting of flights with predictable no-shows.
                                </p>
                            </div>
                        @endif

                        @if($stats['overbooked_percentage'] < 10)
                            <div class="p-3 bg-blue-50 border-l-4 border-blue-500">
                                <p class="text-sm text-blue-800">
                                    <strong>💼 Opportunity:</strong> Only {{ $stats['overbooked_percentage'] }}% of flights are using overbooking. Consider enabling it on more routes to maximize revenue.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
