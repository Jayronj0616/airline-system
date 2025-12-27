<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pricing Management') }}
            </h2>
            <form method="POST" action="{{ route('admin.pricing.recalculate-all') }}">
                @csrf
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                        onclick="return confirm('Recalculate prices for all future flights?')">
                    Recalculate All Prices
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

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
                                            Base Fares
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Load Factor
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($flights as $flight)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $flight->flight_number }}</div>
                                                <div class="text-xs text-gray-500">{{ $flight->aircraft->model }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $flight->origin }} → {{ $flight->destination }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $flight->departure_time->format('M d, Y h:i A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-xs space-y-1">
                                                    <div><span class="text-gray-500">Y:</span> ₱{{ number_format($flight->base_price_economy, 2) }}</div>
                                                    <div><span class="text-gray-500">J:</span> ₱{{ number_format($flight->base_price_business, 2) }}</div>
                                                    <div><span class="text-gray-500">F:</span> ₱{{ number_format($flight->base_price_first, 2) }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $flight->load_factor }}%"></div>
                                                    </div>
                                                    <span class="text-sm text-gray-700">{{ $flight->load_factor }}%</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                                <a href="{{ route('admin.pricing.edit', $flight) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900">
                                                    Edit Fares
                                                </a>
                                                <form method="POST" 
                                                      action="{{ route('admin.pricing.recalculate', $flight) }}" 
                                                      class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900">
                                                        Recalculate
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
</x-app-layout>
