<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Flights At Risk of Denied Boarding') }}
            </h2>
            <a href="{{ route('admin.overbooking.index') }}" 
               class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                Back to Overview
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Alert Banner -->
            <div class="bg-red-100 border border-red-400 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-red-900 mb-2">⚠️ Critical: Manual Intervention Required</h3>
                <p class="text-sm text-red-800">
                    These flights have more confirmed bookings than physical capacity. 
                    Monitor these flights closely and prepare for potential denied boarding situations.
                </p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($flightsWithStats->isEmpty())
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">✅</div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Flights At Risk</h3>
                            <p class="text-gray-600">All flights are within safe overbooking limits.</p>
                        </div>
                    @else
                        <h3 class="text-lg font-semibold mb-4">
                            {{ $flightsWithStats->count() }} Flight(s) Require Attention
                        </h3>
                        
                        <div class="space-y-6">
                            @foreach($flightsWithStats as $flight)
                                @php
                                    $stats = $flight->overbooking_stats;
                                    $risk = $flight->risk_assessment;
                                    
                                    $riskColor = match($risk['risk_level']) {
                                        'high' => 'border-red-300 bg-red-50',
                                        'medium' => 'border-yellow-300 bg-yellow-50',
                                        'low' => 'border-green-300 bg-green-50',
                                        default => 'border-gray-300 bg-gray-50',
                                    };
                                @endphp
                                
                                <div class="border-2 rounded-lg p-6 {{ $riskColor }}">
                                    <!-- Flight Header -->
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h4 class="text-xl font-bold text-gray-900">{{ $flight->flight_number }}</h4>
                                            <p class="text-gray-600">{{ $flight->origin }} → {{ $flight->destination }}</p>
                                            <p class="text-sm text-gray-500">{{ $flight->departure_time->format('M d, Y h:i A') }}</p>
                                        </div>
                                        <div class="text-right">
                                            @php
                                                $riskBadgeColor = match($risk['risk_level']) {
                                                    'high' => 'bg-red-600',
                                                    'medium' => 'bg-yellow-600',
                                                    'low' => 'bg-green-600',
                                                    default => 'bg-gray-600',
                                                };
                                            @endphp
                                            <span class="px-3 py-1 text-sm font-bold rounded-full text-white {{ $riskBadgeColor }}">
                                                {{ strtoupper($risk['risk_level']) }} RISK
                                            </span>
                                            <p class="text-2xl font-bold text-gray-900 mt-2">{{ round($risk['risk_score']) }}</p>
                                            <p class="text-xs text-gray-600">Risk Score</p>
                                        </div>
                                    </div>

                                    <!-- Statistics Grid -->
                                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
                                        <div>
                                            <div class="text-xs text-gray-600">Physical Capacity</div>
                                            <div class="text-lg font-bold text-gray-900">{{ $stats['physical_capacity'] }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-600">Virtual Capacity</div>
                                            <div class="text-lg font-bold text-indigo-600">{{ $stats['virtual_capacity'] }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-600">Confirmed Bookings</div>
                                            <div class="text-lg font-bold text-red-600">{{ $stats['confirmed_bookings'] }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-600">Overbooked By</div>
                                            <div class="text-lg font-bold text-red-600">+{{ $stats['overbooked_count'] }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-600">Load Factor</div>
                                            <div class="text-lg font-bold {{ $stats['load_factor'] > 100 ? 'text-red-600' : 'text-green-600' }}">
                                                {{ $stats['load_factor'] }}%
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Risk Message -->
                                    <div class="p-3 bg-white border-l-4 {{ $risk['risk_level'] === 'high' ? 'border-red-600' : 'border-yellow-600' }} mb-4">
                                        <p class="text-sm font-semibold text-gray-900">{{ $risk['message'] }}</p>
                                    </div>

                                    <!-- Timeline -->
                                    <div class="flex items-center space-x-4 mb-4 text-sm">
                                        <div>
                                            <span class="text-gray-600">Departure in:</span>
                                            <span class="font-semibold text-gray-900">{{ $flight->days_until_departure }} days</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">|</span>
                                        </div>
                                        <div>
                                            <span class="font-semibold text-gray-900">{{ $flight->hours_until_departure }} hours</span>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex space-x-3">
                                        <a href="{{ route('admin.overbooking.edit', $flight) }}" 
                                           class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-semibold">
                                            View Full Details
                                        </a>
                                        
                                        @if($flight->overbooking_enabled)
                                            <form method="POST" action="{{ route('admin.overbooking.toggle', $flight) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="enabled" value="0">
                                                <button type="submit" 
                                                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-semibold"
                                                        onclick="return confirm('Disable overbooking for this flight?')">
                                                    Disable Overbooking
                                                </button>
                                            </form>
                                        @endif

                                        @if($flight->hours_until_departure <= 2)
                                            <span class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-md text-sm font-semibold border border-yellow-300">
                                                🚨 Boarding Window - Manual Resolution Required
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Recommendations -->
                                    @if($risk['risk_level'] === 'high')
                                        <div class="mt-4 p-3 bg-red-100 border border-red-300 rounded">
                                            <h5 class="font-semibold text-red-900 mb-2">⚠️ Recommended Actions:</h5>
                                            <ul class="text-sm text-red-800 space-y-1 list-disc list-inside">
                                                <li>Contact passengers to request volunteers for alternative flights</li>
                                                <li>Prepare compensation packages (vouchers, hotel, rebooking)</li>
                                                <li>Monitor check-in status closely</li>
                                                <li>Have customer service team on standby</li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
