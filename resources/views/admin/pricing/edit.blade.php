<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Base Fares') }} - {{ $flight->flight_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Flight Information</h3>
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-gray-500">Flight Number</dt>
                            <dd class="text-base font-medium">{{ $flight->flight_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Aircraft</dt>
                            <dd class="text-base font-medium">{{ $flight->aircraft->model }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Route</dt>
                            <dd class="text-base font-medium">{{ $flight->origin }} → {{ $flight->destination }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Departure</dt>
                            <dd class="text-base font-medium">{{ $flight->departure_time->format('M d, Y h:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Load Factor</dt>
                            <dd class="text-base font-medium">{{ $flight->load_factor }}%</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Demand Score</dt>
                            <dd class="text-base font-medium">{{ $flight->demand_score }}/100</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Base Fare Prices</h3>
                    
                    <form method="POST" action="{{ route('admin.pricing.update', $flight) }}" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="base_price_economy" class="block text-sm font-medium text-gray-700">
                                Economy Class Base Fare (₱)
                            </label>
                            <input type="number" 
                                   name="base_price_economy" 
                                   id="base_price_economy" 
                                   step="0.01"
                                   value="{{ old('base_price_economy', $flight->base_price_economy) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('base_price_economy') border-red-500 @enderror">
                            @error('base_price_economy')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">
                                Current: ₱{{ number_format($flight->base_price_economy, 2) }}
                            </p>
                        </div>

                        <div>
                            <label for="base_price_business" class="block text-sm font-medium text-gray-700">
                                Business Class Base Fare (₱)
                            </label>
                            <input type="number" 
                                   name="base_price_business" 
                                   id="base_price_business" 
                                   step="0.01"
                                   value="{{ old('base_price_business', $flight->base_price_business) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('base_price_business') border-red-500 @enderror">
                            @error('base_price_business')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">
                                Current: ₱{{ number_format($flight->base_price_business, 2) }}
                            </p>
                        </div>

                        <div>
                            <label for="base_price_first" class="block text-sm font-medium text-gray-700">
                                First Class Base Fare (₱)
                            </label>
                            <input type="number" 
                                   name="base_price_first" 
                                   id="base_price_first" 
                                   step="0.01"
                                   value="{{ old('base_price_first', $flight->base_price_first) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('base_price_first') border-red-500 @enderror">
                            @error('base_price_first')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">
                                Current: ₱{{ number_format($flight->base_price_first, 2) }}
                            </p>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                            <h4 class="text-sm font-semibold text-blue-900 mb-2">Pricing Formula</h4>
                            <p class="text-xs text-blue-800">
                                Final Price = Base Fare × Time Factor × Inventory Factor × Demand Factor
                            </p>
                            <p class="text-xs text-blue-700 mt-2">
                                After updating base fares, prices will be automatically recalculated based on current time, inventory, and demand factors.
                            </p>
                        </div>

                        <div class="flex items-center justify-between pt-4">
                            <a href="{{ route('admin.pricing.index') }}" 
                               class="text-gray-600 hover:text-gray-900">
                                ← Back to List
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                Update Base Fares
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
