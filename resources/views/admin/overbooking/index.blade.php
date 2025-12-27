<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Overbooking Management') }}
                </h2>
                @if($atRiskCount > 0)
                    <p class="text-sm text-red-600 mt-1">
                        ⚠️ {{ $atRiskCount }} flight(s) at risk of denied boarding
                    </p>
                @endif
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.overbooking.reports') }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    📊 View Reports
                </a>
                <a href="{{ route('admin.overbooking.at-risk') }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    View At-Risk Flights
                </a>
                <button onclick="document.getElementById('globalEnableModal').classList.remove('hidden')" 
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Enable Globally
                </button>
                <form method="POST" action="{{ route('admin.overbooking.disable-global') }}" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                            onclick="return confirm('Disable overbooking for all flights?')">
                        Disable Globally
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Info Panel -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-900 mb-2">📋 Overbooking Guidelines</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Overbooking can only be enabled for flights <strong>>7 days</strong> away</li>
                    <li>• Maximum overbooking percentage: <strong>15%</strong></li>
                    <li>• Overbooking auto-disables <strong>48 hours</strong> before departure</li>
                    <li>• Monitor flights at risk of denied boarding regularly</li>
                </ul>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Upcoming Flights</h3>
                    
                    @if($flights->isEmpty())
                        <p class="text-gray-500 text-center py-8">No upcoming flights.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Flight
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Route
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Departure
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Capacity
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Overbooking
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Risk Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($flights as $flight)
                                        @php
                                            $stats = $flight->overbooking_stats;
                                            $risk = $flight->risk_assessment;
                                            
                                            $riskColor = match($risk['risk_level']) {
                                                'high' => 'bg-red-100 text-red-800',
                                                'medium' => 'bg-yellow-100 text-yellow-800',
                                                'low' => 'bg-green-100 text-green-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $flight->flight_number }}</div>
                                                <div class="text-xs text-gray-500">{{ $flight->aircraft->model }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $flight->origin }} → {{ $flight->destination }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $flight->departure_time->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-500">{{ $flight->departure_time->format('h:i A') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-xs space-y-1">
                                                    <div><span class="text-gray-500">Physical:</span> {{ $stats['physical_capacity'] }}</div>
                                                    <div><span class="text-gray-500">Virtual:</span> {{ $stats['virtual_capacity'] }}</div>
                                                    <div><span class="text-gray-500">Confirmed:</span> {{ $stats['confirmed_bookings'] }}</div>
                                                    <div class="font-semibold">
                                                        <span class="text-gray-500">Load:</span> 
                                                        <span class="{{ $stats['load_factor'] > 100 ? 'text-red-600' : 'text-gray-900' }}">
                                                            {{ $stats['load_factor'] }}%
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($flight->overbooking_enabled)
                                                    <div class="flex items-center space-x-2">
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                            Enabled
                                                        </span>
                                                        <span class="text-xs text-gray-600">
                                                            {{ $flight->overbooking_percentage }}%
                                                        </span>
                                                    </div>
                                                    @if($stats['overbooked_count'] > 0)
                                                        <div class="text-xs text-red-600 mt-1">
                                                            +{{ $stats['overbooked_count'] }} overbooked
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Disabled
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $riskColor }}">
                                                    {{ ucfirst($risk['risk_level']) }}
                                                </span>
                                                @if($risk['risk_level'] !== 'none')
                                                    <div class="text-xs text-gray-600 mt-1">
                                                        {{ $risk['message'] }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm space-y-1">
                                                <a href="{{ route('admin.overbooking.edit', $flight) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900 block">
                                                    View Details
                                                </a>
                                                
                                                <form method="POST" 
                                                      action="{{ route('admin.overbooking.toggle', $flight) }}" 
                                                      class="inline">
                                                    @csrf
                                                    <input type="hidden" name="enabled" value="{{ $flight->overbooking_enabled ? '0' : '1' }}">
                                                    @if(!$flight->overbooking_enabled)
                                                        <input type="hidden" name="percentage" value="10">
                                                    @endif
                                                    <button type="submit" class="{{ $flight->overbooking_enabled ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}">
                                                        {{ $flight->overbooking_enabled ? 'Disable' : 'Enable' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $flights->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Global Enable Modal -->
    <div id="globalEnableModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Enable Overbooking Globally</h3>
                <form method="POST" action="{{ route('admin.overbooking.enable-global') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="percentage" class="block text-sm font-medium text-gray-700 mb-2">
                            Overbooking Percentage (0-15%)
                        </label>
                        <input type="number" 
                               name="percentage" 
                               id="percentage" 
                               min="0" 
                               max="15" 
                               step="0.1" 
                               value="10"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">
                            Only flights >7 days away will be enabled
                        </p>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" 
                                onclick="document.getElementById('globalEnableModal').classList.add('hidden')"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Enable
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
