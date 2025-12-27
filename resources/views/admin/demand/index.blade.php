<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Demand Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(!$selectedFlight)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-500">
                        <p>No flights available for analysis.</p>
                    </div>
                </div>
            @else
                
                <!-- Filters Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form method="GET" action="{{ route('admin.demand.index') }}" class="flex flex-wrap gap-4 items-end">
                            
                            <!-- Flight Selection -->
                            <div class="flex-1 min-w-[250px]">
                                <label for="flight_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Select Flight
                                </label>
                                <select name="flight_id" id="flight_id" 
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($flights as $flight)
                                        <option value="{{ $flight->id }}" 
                                                {{ $selectedFlight->id == $flight->id ? 'selected' : '' }}>
                                            {{ $flight->flight_number }} - {{ $flight->origin }} → {{ $flight->destination }} 
                                            ({{ $flight->departure_time->format('M d, Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Days Range -->
                            <div class="flex-none">
                                <label for="days" class="block text-sm font-medium text-gray-700 mb-1">
                                    Days to Show
                                </label>
                                <select name="days" id="days" 
                                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 Days</option>
                                    <option value="14" {{ $days == 14 ? 'selected' : '' }}>Last 14 Days</option>
                                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 Days</option>
                                </select>
                            </div>
                            
                            <!-- Submit -->
                            <div class="flex-none">
                                <button type="submit" 
                                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                                    Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Flight Statistics Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Flight Overview</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            
                            <!-- Current Demand Score -->
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4">
                                <div class="text-sm text-gray-600 mb-1">Current Demand Score</div>
                                <div class="text-3xl font-bold text-blue-700">
                                    {{ $flightStats['current_demand_score'] }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Level: <span class="font-semibold">{{ $flightStats['demand_level'] }}</span>
                                </div>
                            </div>
                            
                            <!-- Demand Multiplier -->
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4">
                                <div class="text-sm text-gray-600 mb-1">Demand Multiplier</div>
                                <div class="text-3xl font-bold text-purple-700">
                                    {{ $flightStats['demand_multiplier'] }}x
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Price Impact Factor
                                </div>
                            </div>
                            
                            <!-- Load Factor -->
                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4">
                                <div class="text-sm text-gray-600 mb-1">Load Factor</div>
                                <div class="text-3xl font-bold text-green-700">
                                    {{ $flightStats['load_factor'] }}%
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $flightStats['booked_seats'] }} / {{ $flightStats['total_seats'] }} seats
                                </div>
                            </div>
                            
                            <!-- Days Until Departure -->
                            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4">
                                <div class="text-sm text-gray-600 mb-1">Days Until Departure</div>
                                <div class="text-3xl font-bold text-orange-700">
                                    {{ $flightStats['days_until_departure'] }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $selectedFlight->departure_time->format('M d, Y h:i A') }}
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>

                <!-- Demand Trend Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Demand Score Trend</h3>
                        <div class="relative h-80">
                            <canvas id="demandChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Price vs Demand Correlation Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Price vs Demand Correlation (Economy Class)</h3>
                        <div class="relative h-80">
                            <canvas id="priceCorrelationChart"></canvas>
                        </div>
                    </div>
                </div>

            @endif

        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    @if($selectedFlight)
    <script>
        // Demand Trend Chart
        const demandCtx = document.getElementById('demandChart').getContext('2d');
        const demandChart = new Chart(demandCtx, {
            type: 'line',
            data: {
                labels: @json($demandData['labels']),
                datasets: [
                    {
                        label: 'Average Demand Factor',
                        data: @json($demandData['avgDemand']),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: 'Max Demand Factor',
                        data: @json($demandData['maxDemand']),
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.05)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.4,
                        fill: false,
                    },
                    {
                        label: 'Min Demand Factor',
                        data: @json($demandData['minDemand']),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.05)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.4,
                        fill: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 2,
                        title: {
                            display: true,
                            text: 'Demand Factor (Multiplier)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(1) + 'x';
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });

        // Price vs Demand Correlation Chart
        const priceCtx = document.getElementById('priceCorrelationChart').getContext('2d');
        const priceChart = new Chart(priceCtx, {
            type: 'line',
            data: {
                labels: @json($priceData['labels']),
                datasets: [
                    {
                        label: 'Average Price (₱)',
                        data: @json($priceData['prices']),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Demand Factor',
                        data: @json($priceData['demand']),
                        borderColor: 'rgb(139, 92, 246)',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    if (context.datasetIndex === 0) {
                                        label += '₱' + context.parsed.y.toFixed(2);
                                    } else {
                                        label += context.parsed.y.toFixed(2) + 'x';
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Price (₱)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toFixed(0);
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Demand Factor (Multiplier)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(1) + 'x';
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });
    </script>
    @endif
</x-app-layout>
