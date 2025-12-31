<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Flight Status Results') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search Again -->
            <div class="mb-6">
                <a href="{{ route('flight-status.index') }}" class="text-blue-600 hover:text-blue-800">
                    ← Search Again
                </a>
            </div>

            @if($flights->isEmpty())
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <div class="text-gray-400 text-6xl mb-4">✈</div>
                    <h3 class="text-xl font-semibold mb-2">No Flights Found</h3>
                    <p class="text-gray-600 mb-6">Try adjusting your search criteria</p>
                    <a href="{{ route('flight-status.index') }}" 
                       class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                        New Search
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($flights as $flight)
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900">{{ $flight->flight_number }}</h3>
                                        <p class="text-gray-600">{{ $flight->aircraft->model }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold
                                            {{ $flight->status_info['color'] === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $flight->status_info['color'] === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $flight->status_info['color'] === 'orange' ? 'bg-orange-100 text-orange-800' : '' }}
                                            {{ $flight->status_info['color'] === 'purple' ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ $flight->status_info['color'] === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ $flight->status_info['icon'] }} {{ $flight->status_info['status'] }}
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                                    <!-- Origin -->
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Departure</p>
                                        <p class="text-xl font-bold">{{ $flight->origin }}</p>
                                        <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($flight->departure_time)->format('H:i') }}</p>
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($flight->departure_time)->format('M d, Y') }}</p>
                                    </div>

                                    <!-- Duration -->
                                    <div class="flex items-center justify-center">
                                        <div class="text-center">
                                            <div class="text-gray-400 mb-2">
                                                {{ $flight->origin }} ✈ {{ $flight->destination }}
                                            </div>
                                            @if(isset($flight->status_info['progress']))
                                                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                                    <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $flight->status_info['progress'] }}%"></div>
                                                </div>
                                                <p class="text-xs text-gray-600">{{ $flight->status_info['progress'] }}% Complete</p>
                                            @else
                                                <p class="text-sm text-gray-600">
                                                    {{ \Carbon\Carbon::parse($flight->departure_time)->diffInHours(\Carbon\Carbon::parse($flight->arrival_time)) }}h 
                                                    {{ \Carbon\Carbon::parse($flight->departure_time)->diffInMinutes(\Carbon\Carbon::parse($flight->arrival_time)) % 60 }}m
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Destination -->
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500 mb-1">Arrival</p>
                                        <p class="text-xl font-bold">{{ $flight->destination }}</p>
                                        <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($flight->arrival_time)->format('H:i') }}</p>
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($flight->arrival_time)->format('M d, Y') }}</p>
                                    </div>
                                </div>

                                <!-- Status Message -->
                                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                    <p class="text-sm text-gray-700">
                                        <span class="font-semibold">Status:</span> {{ $flight->status_info['message'] }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex justify-between items-center">
                                    <div class="text-sm text-gray-600">
                                        <span class="font-semibold">Aircraft:</span> {{ $flight->aircraft->registration }}
                                    </div>
                                    <a href="{{ route('flight-status.show', $flight) }}" 
                                       class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                        View Details →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
