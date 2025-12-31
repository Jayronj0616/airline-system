<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Price Calendar') }} - {{ $origin }} to {{ $destination }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Date Navigation -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between">
                    <a href="{{ route('price-calendar.show', ['origin' => $origin, 'destination' => $destination, 'departure_date' => $baseDate->copy()->subDays(7)->format('Y-m-d')]) }}" 
                       class="text-blue-600 hover:text-blue-800 font-semibold">
                        ← Previous Week
                    </a>
                    <h3 class="text-lg font-semibold">
                        {{ $baseDate->format('M d') }} - {{ $baseDate->copy()->addDays(6)->format('M d, Y') }}
                    </h3>
                    <a href="{{ route('price-calendar.show', ['origin' => $origin, 'destination' => $destination, 'departure_date' => $baseDate->copy()->addDays(7)->format('Y-m-d')]) }}" 
                       class="text-blue-600 hover:text-blue-800 font-semibold">
                        Next Week →
                    </a>
                </div>
            </div>

            <!-- Legend -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="font-semibold mb-4">Price Range</h3>
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-green-100 border-2 border-green-500 rounded"></div>
                        <span class="text-sm">Lowest Price</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-yellow-100 border-2 border-yellow-500 rounded"></div>
                        <span class="text-sm">Medium Price</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-red-100 border-2 border-red-500 rounded"></div>
                        <span class="text-sm">Highest Price</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-gray-100 border-2 border-gray-300 rounded"></div>
                        <span class="text-sm">No Flights</span>
                    </div>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
                @php
                    $allPrices = [];
                    foreach ($calendar as $day) {
                        foreach ($day['prices'] as $classData) {
                            $allPrices[] = $classData['price'];
                        }
                    }
                    $minPrice = count($allPrices) > 0 ? min($allPrices) : 0;
                    $maxPrice = count($allPrices) > 0 ? max($allPrices) : 0;
                    $midPrice = count($allPrices) > 0 ? ($minPrice + $maxPrice) / 2 : 0;
                @endphp

                @foreach($calendar as $day)
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition">
                        <!-- Date Header -->
                        <div class="bg-gray-50 p-3 border-b">
                            <p class="text-xs text-gray-600">{{ $day['date']->format('D') }}</p>
                            <p class="text-lg font-bold">{{ $day['date']->format('M d') }}</p>
                        </div>

                        <!-- Prices -->
                        <div class="p-4">
                            @if(count($day['prices']) > 0)
                                @foreach($day['prices'] as $className => $classData)
                                    @php
                                        $price = $classData['price'];
                                        if ($price <= $minPrice + ($midPrice - $minPrice) / 2) {
                                            $colorClass = 'bg-green-100 border-green-500 text-green-900';
                                        } elseif ($price <= $midPrice + ($maxPrice - $midPrice) / 2) {
                                            $colorClass = 'bg-yellow-100 border-yellow-500 text-yellow-900';
                                        } else {
                                            $colorClass = 'bg-red-100 border-red-500 text-red-900';
                                        }
                                    @endphp
                                    <div class="mb-3 pb-3 border-b border-gray-200 last:border-0 last:mb-0 last:pb-0">
                                        <p class="text-xs text-gray-600 mb-1">{{ $className }}</p>
                                        <div class="flex items-center justify-between">
                                            <div class="px-3 py-2 rounded-lg border-2 {{ $colorClass }}">
                                                <p class="text-lg font-bold">
                                                    ${{ number_format($price, 0) }}
                                                </p>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">{{ $classData['available'] }} seats</p>
                                        <a href="{{ route('flights.show', $classData['flight']) }}" 
                                           class="text-xs text-blue-600 hover:text-blue-800 mt-1 block">
                                            View Flight →
                                        </a>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-8">
                                    <div class="text-gray-400 text-3xl mb-2">✈️</div>
                                    <p class="text-sm text-gray-500">No flights</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Search Another Route -->
            <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                <h3 class="font-semibold mb-4">Search Another Route</h3>
                <form method="GET" action="{{ route('price-calendar.show') }}" class="flex gap-4">
                    <input type="text" name="origin" placeholder="From (e.g., DOH)" 
                           value="{{ $origin }}"
                           maxlength="3" required
                           class="flex-1 rounded-lg border-gray-300 uppercase">
                    <input type="text" name="destination" placeholder="To (e.g., MNL)" 
                           value="{{ $destination }}"
                           maxlength="3" required
                           class="flex-1 rounded-lg border-gray-300 uppercase">
                    <input type="date" name="departure_date" 
                           value="{{ $baseDate->format('Y-m-d') }}"
                           class="flex-1 rounded-lg border-gray-300">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                        View Calendar
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
