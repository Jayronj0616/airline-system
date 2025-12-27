<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Overbooking Management - {{ $flight->flight_number }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $flight->origin }} → {{ $flight->destination }} | {{ $flight->departure_time->format('M d, Y h:i A') }}
                </p>
            </div>
            <a href="{{ route('admin.overbooking.index') }}" 
               class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Risk Assessment Banner -->
            @php
                $riskColor = match($riskAssessment['risk_level']) {
                    'high' => 'bg-red-100 border-red-400 text-red-800',
                    'medium' => 'bg-yellow-100 border-yellow-400 text-yellow-800',
                    'low' => 'bg-green-100 border-green-400 text-green-800',
                    default => 'bg-gray-100 border-gray-400 text-gray-800',
                };
            @endphp
            
            <div class="p-4 border rounded-lg {{ $riskColor }}">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-lg">Risk Level: {{ ucfirst($riskAssessment['risk_level']) }}</h3>
                        <p class="text-sm mt-1">{{ $riskAssessment['message'] }}</p>
                    </div>
                    @if($riskAssessment['risk_level'] !== 'none')
                        <span class="text-2xl font-bold">{{ round($riskAssessment['risk_score']) }}</span>
                    @endif
                </div>
            </div>

            <!-- Key Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Physical Capacity</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['physical_capacity'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Actual seats</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Virtual Capacity</div>
                    <div class="text-3xl font-bold text-indigo-600">{{ $stats['virtual_capacity'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">With overbooking</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Confirmed Bookings</div>
                    <div class="text-3xl font-bold {{ $stats['confirmed_bookings'] > $stats['physical_capacity'] ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $stats['confirmed_bookings'] }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Current bookings</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Load Factor</div>
                    <div class="text-3xl font-bold {{ $stats['load_factor'] > 100 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $stats['load_factor'] }}%
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Capacity utilization</div>
                </div>
            </div>

            <!-- Overbooking Status -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 mb-2">Overbooking Status</div>
                    @if($flight->overbooking_enabled)
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                            Enabled ({{ $flight->overbooking_percentage }}%)
                        </span>
                    @else
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                            Disabled
                        </span>
                    @endif
                    
                    @if(!$stats['can_overbook'] && $flight->overbooking_enabled)
                        <p class="text-xs text-yellow-600 mt-2">⚠️ Auto-disabled (flight too close to departure)</p>
                    @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 mb-2">Overbooked Count</div>
                    @if($stats['overbooked_count'] > 0)
                        <div class="text-2xl font-bold text-red-600">+{{ $stats['overbooked_count'] }}</div>
                        <p class="text-xs text-gray-500 mt-1">seats over physical capacity</p>
                    @else
                        <div class="text-2xl font-bold text-green-600">0</div>
                        <p class="text-xs text-gray-500 mt-1">No overbooking</p>
                    @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 mb-2">Available Seats</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['available_seats'] }}</div>
                    <p class="text-xs text-gray-500 mt-1">Seats available for booking</p>
                </div>
            </div>

            <!-- Overbooking Controls -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Overbooking Controls</h3>
                    
                    <div class="space-y-6">
                        <!-- Toggle Overbooking -->
                        <div>
                            <form method="POST" action="{{ route('admin.overbooking.toggle', $flight) }}" class="flex items-center space-x-4">
                                @csrf
                                <input type="hidden" name="enabled" value="{{ $flight->overbooking_enabled ? '0' : '1' }}">
                                @if(!$flight->overbooking_enabled)
                                    <input type="hidden" name="percentage" value="10">
                                @endif
                                
                                <button type="submit" 
                                        class="px-6 py-3 {{ $flight->overbooking_enabled ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-md font-semibold">
                                    {{ $flight->overbooking_enabled ? 'Disable Overbooking' : 'Enable Overbooking' }}
                                </button>
                                
                                @if($flight->days_until_departure < 7)
                                    <span class="text-sm text-yellow-600">
                                        ⚠️ Flight is less than 7 days away. Overbooking cannot be enabled.
                                    </span>
                                @endif
                            </form>
                        </div>

                        <!-- Update Percentage -->
                        @if($flight->overbooking_enabled)
                            <div class="border-t pt-6">
                                <form method="POST" action="{{ route('admin.overbooking.update-percentage', $flight) }}">
                                    @csrf
                                    <div class="flex items-end space-x-4">
                                        <div class="flex-1">
                                            <label for="overbooking_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                                                Overbooking Percentage (0-15%)
                                            </label>
                                            <input type="number" 
                                                   name="overbooking_percentage" 
                                                   id="overbooking_percentage" 
                                                   min="0" 
                                                   max="15" 
                                                   step="0.1" 
                                                   value="{{ $flight->overbooking_percentage }}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <button type="submit" 
                                                class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                            Update
                                        </button>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-2">
                                        Recommended: <strong>{{ round($recommendedPercentage, 1) }}%</strong> (based on no-show probability)
                                    </p>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- No-Show Analysis -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">No-Show Analysis</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-sm text-gray-500 mb-2">Expected No-Shows</div>
                            <div class="text-2xl font-bold text-gray-900">{{ round($expectedNoShows, 1) }}</div>
                            <p class="text-xs text-gray-500 mt-1">Based on historical fare class data</p>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 mb-2">Recommended Overbooking</div>
                            <div class="text-2xl font-bold text-indigo-600">{{ round($recommendedPercentage, 1) }}%</div>
                            <p class="text-xs text-gray-500 mt-1">Safe level with 20% safety margin</p>
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded">
                        <h4 class="font-semibold text-blue-900 mb-2">💡 Interpretation</h4>
                        <p class="text-sm text-blue-800">
                            With {{ round($expectedNoShows, 1) }} expected no-shows, you can safely overbook by {{ round($recommendedPercentage, 1) }}%.
                            Current overbooking: {{ $flight->overbooking_enabled ? $flight->overbooking_percentage . '%' : 'Disabled' }}.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Denied Boarding History -->
            @if($deniedBoardings->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Denied Boarding History</h3>
                        
                        <div class="space-y-4">
                            @foreach($deniedBoardings as $deniedBoarding)
                                <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="font-semibold text-gray-900">
                                                Booking #{{ $deniedBoarding->booking_id }}
                                            </div>
                                            <div class="text-sm text-gray-600 mt-1">
                                                User: {{ $deniedBoarding->booking->user->name }}
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                Denied at: {{ $deniedBoarding->denied_at->format('M d, Y h:i A') }}
                                            </div>
                                            @if($deniedBoarding->notes)
                                                <div class="text-sm text-gray-600 mt-2">
                                                    Notes: {{ $deniedBoarding->notes }}
                                                </div>
                                            @endif
                                        </div>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-{{ $deniedBoarding->resolution_type === 'compensated' ? 'green' : 'yellow' }}-100 text-{{ $deniedBoarding->resolution_type === 'compensated' ? 'green' : 'yellow' }}-800">
                                            {{ ucfirst($deniedBoarding->resolution_type) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Flight Timeline -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Flight Timeline</h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-500 w-32">Days until departure:</div>
                            <div class="font-semibold text-gray-900">{{ $flight->days_until_departure }} days</div>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-500 w-32">Hours until departure:</div>
                            <div class="font-semibold text-gray-900">{{ $flight->hours_until_departure }} hours</div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-500 w-32">Can overbook:</div>
                            <div class="font-semibold {{ $stats['can_overbook'] ? 'text-green-600' : 'text-red-600' }}">
                                {{ $stats['can_overbook'] ? 'Yes' : 'No' }}
                            </div>
                        </div>

                        @if(!$stats['can_overbook'] && $flight->days_until_departure >= 0)
                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-800">
                                ⚠️ Overbooking is disabled because:
                                @if($flight->days_until_departure < 7)
                                    Flight is less than 7 days away
                                @elseif($flight->hours_until_departure < 48)
                                    Flight is less than 48 hours away
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
