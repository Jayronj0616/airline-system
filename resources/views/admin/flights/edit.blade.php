<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Flight: {{ $flight->flight_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.flights.update', $flight) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Flight Number</label>
                            <input type="text" name="flight_number" value="{{ old('flight_number', $flight->flight_number) }}" required
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
                                    <option value="{{ $a->id }}" {{ old('aircraft_id', $flight->aircraft_id) == $a->id ? 'selected' : '' }}>
                                        {{ $a->name }} ({{ $a->total_seats }} seats)
                                    </option>
                                @endforeach
                            </select>
                            @error('aircraft_id')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                            @if($flight->bookings()->exists())
                                <p class="text-yellow-600 text-xs mt-1">⚠️ Warning: Changing aircraft will regenerate available seats</p>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Origin (Airport Code)</label>
                                <input type="text" name="origin" value="{{ old('origin', $flight->origin) }}" maxlength="3" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 uppercase leading-tight focus:outline-none focus:shadow-outline @error('origin') border-red-500 @enderror">
                                @error('origin')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Destination (Airport Code)</label>
                                <input type="text" name="destination" value="{{ old('destination', $flight->destination) }}" maxlength="3" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 uppercase leading-tight focus:outline-none focus:shadow-outline @error('destination') border-red-500 @enderror">
                                @error('destination')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Departure Time</label>
                                <input type="datetime-local" name="departure_time" value="{{ old('departure_time', $flight->departure_time->format('Y-m-d\TH:i')) }}" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('departure_time') border-red-500 @enderror">
                                @error('departure_time')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Arrival Time</label>
                                <input type="datetime-local" name="arrival_time" value="{{ old('arrival_time', $flight->arrival_time->format('Y-m-d\TH:i')) }}" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('arrival_time') border-red-500 @enderror">
                                @error('arrival_time')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Economy Price</label>
                                <input type="number" name="base_price_economy" value="{{ old('base_price_economy', $flight->base_price_economy) }}" min="1" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('base_price_economy') border-red-500 @enderror">
                                @error('base_price_economy')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Business Price</label>
                                <input type="number" name="base_price_business" value="{{ old('base_price_business', $flight->base_price_business) }}" min="1" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('base_price_business') border-red-500 @enderror">
                                @error('base_price_business')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">First Class Price</label>
                                <input type="number" name="base_price_first" value="{{ old('base_price_first', $flight->base_price_first) }}" min="1" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('base_price_first') border-red-500 @enderror">
                                @error('base_price_first')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Status</label>
                            <select name="status" required
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('status') border-red-500 @enderror">
                                <option value="scheduled" {{ old('status', $flight->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="delayed" {{ old('status', $flight->status) == 'delayed' ? 'selected' : '' }}>Delayed</option>
                                <option value="cancelled" {{ old('status', $flight->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="boarding" {{ old('status', $flight->status) == 'boarding' ? 'selected' : '' }}>Boarding</option>
                                <option value="departed" {{ old('status', $flight->status) == 'departed' ? 'selected' : '' }}>Departed</option>
                                <option value="arrived" {{ old('status', $flight->status) == 'arrived' ? 'selected' : '' }}>Arrived</option>
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
                                Update Flight
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
