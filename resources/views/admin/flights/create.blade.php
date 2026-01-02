<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Create Flight
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.flights.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Flight Number</label>
                            <input type="text" name="flight_number" value="{{ old('flight_number') }}" required
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('flight_number') border-red-500 @enderror">
                            @error('flight_number')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Aircraft</label>
                            <select name="aircraft_id" required
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('aircraft_id') border-red-500 @enderror">
                                <option value="">Select Aircraft</option>
                                @foreach($aircraft as $a)
                                    <option value="{{ $a->id }}" {{ old('aircraft_id') == $a->id ? 'selected' : '' }}>
                                        {{ $a->name }} ({{ $a->total_seats }} seats)
                                    </option>
                                @endforeach
                            </select>
                            @error('aircraft_id')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Origin (Airport Code)</label>
                                <input type="text" name="origin" value="{{ old('origin') }}" maxlength="3" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 uppercase leading-tight focus:outline-none focus:shadow-outline @error('origin') border-red-500 @enderror">
                                @error('origin')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Destination (Airport Code)</label>
                                <input type="text" name="destination" value="{{ old('destination') }}" maxlength="3" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 uppercase leading-tight focus:outline-none focus:shadow-outline @error('destination') border-red-500 @enderror">
                                @error('destination')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Departure Time</label>
                                <input type="datetime-local" name="departure_time" value="{{ old('departure_time') }}" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('departure_time') border-red-500 @enderror">
                                @error('departure_time')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Arrival Time</label>
                                <input type="datetime-local" name="arrival_time" value="{{ old('arrival_time') }}" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('arrival_time') border-red-500 @enderror">
                                @error('arrival_time')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Economy Price</label>
                                <input type="number" name="base_price_economy" value="{{ old('base_price_economy', 100) }}" min="1" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('base_price_economy') border-red-500 @enderror">
                                @error('base_price_economy')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Business Price</label>
                                <input type="number" name="base_price_business" value="{{ old('base_price_business', 300) }}" min="1" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('base_price_business') border-red-500 @enderror">
                                @error('base_price_business')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">First Class Price</label>
                                <input type="number" name="base_price_first" value="{{ old('base_price_first', 800) }}" min="1" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('base_price_first') border-red-500 @enderror">
                                @error('base_price_first')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Tax (%)</label>
                                <input type="number" step="0.01" name="tax_percentage" value="{{ old('tax_percentage', 12.00) }}" min="0" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Booking Fee</label>
                                <input type="number" step="0.01" name="booking_fee" value="{{ old('booking_fee', 50.00) }}" min="0" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Fuel Surcharge</label>
                                <input type="number" step="0.01" name="fuel_surcharge" value="{{ old('fuel_surcharge', 100.00) }}" min="0" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Status</label>
                            <select name="status" required
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('status') border-red-500 @enderror">
                                <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="delayed" {{ old('status') == 'delayed' ? 'selected' : '' }}>Delayed</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <a href="{{ route('admin.flights.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Create Flight
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
