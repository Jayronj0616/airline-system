<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Flight Status') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-6">Check Flight Status</h3>

                    <form method="GET" action="{{ route('flight-status.search') }}" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Flight Number -->
                            <div>
                                <label for="flight_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Flight Number
                                </label>
                                <input type="text" 
                                       name="flight_number" 
                                       id="flight_number" 
                                       value="{{ request('flight_number') }}"
                                       placeholder="e.g., QR123"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Date -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Date
                                </label>
                                <input type="date" 
                                       name="date" 
                                       id="date" 
                                       value="{{ request('date', date('Y-m-d')) }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Origin -->
                            <div>
                                <label for="origin" class="block text-sm font-medium text-gray-700 mb-2">
                                    Origin (3-letter code)
                                </label>
                                <input type="text" 
                                       name="origin" 
                                       id="origin" 
                                       value="{{ request('origin') }}"
                                       placeholder="e.g., DOH"
                                       maxlength="3"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase">
                            </div>

                            <!-- Destination -->
                            <div>
                                <label for="destination" class="block text-sm font-medium text-gray-700 mb-2">
                                    Destination (3-letter code)
                                </label>
                                <input type="text" 
                                       name="destination" 
                                       id="destination" 
                                       value="{{ request('destination') }}"
                                       placeholder="e.g., MNL"
                                       maxlength="3"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition">
                                Search Flights
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="text-blue-600 text-3xl mb-2">✈</div>
                    <h4 class="font-semibold mb-2">Real-time Updates</h4>
                    <p class="text-sm text-gray-600">Track your flight status in real-time with live updates</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="text-green-600 text-3xl mb-2">🔔</div>
                    <h4 class="font-semibold mb-2">Flight Alerts</h4>
                    <p class="text-sm text-gray-600">Get notified about delays, gate changes, and more</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="text-purple-600 text-3xl mb-2">📱</div>
                    <h4 class="font-semibold mb-2">Mobile Friendly</h4>
                    <p class="text-sm text-gray-600">Check flight status on any device, anywhere</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
