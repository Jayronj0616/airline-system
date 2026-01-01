<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Review Your Selection
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Flight Details -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Flight Details</h3>
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-bold text-xl">{{ $flight->flight_number }}</p>
                                    <p class="text-gray-600">{{ $flight->aircraft->name ?? 'N/A' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">{{ $flight->departure_time->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="flex justify-between mt-4">
                                <div>
                                    <p class="text-2xl font-bold">{{ $flight->origin }}</p>
                                    <p class="text-sm text-gray-600">{{ $flight->departure_time->format('H:i') }}</p>
                                </div>
                                <div class="flex-1 flex items-center justify-center px-4">
                                    <div class="border-t border-gray-300 flex-1"></div>
                                    <svg class="w-6 h-6 mx-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                                    </svg>
                                    <div class="border-t border-gray-300 flex-1"></div>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold">{{ $flight->destination }}</p>
                                    <p class="text-sm text-gray-600">{{ $flight->arrival_time->format('H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fare Class Details -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Fare Class: {{ $fareClass->name }}</h3>
                        <div class="border rounded-lg p-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Cabin Type</p>
                                    <p class="font-semibold">{{ ucfirst($fareClass->cabin_type) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Baggage Allowance</p>
                                    <p class="font-semibold">{{ $fareClass->fareRule->baggage_allowance ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Refundable</p>
                                    <p class="font-semibold">{{ $fareClass->fareRule->is_refundable ? 'Yes' : 'No' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Changes Allowed</p>
                                    <p class="font-semibold">{{ $fareClass->fareRule->change_fee ? 'Yes (Fee: $' . $fareClass->fareRule->change_fee . ')' : 'No' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Price Breakdown</h3>
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between mb-2">
                                <span>Fare per passenger</span>
                                <span class="font-semibold">${{ number_format($pricePerPerson, 2) }}</span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span>Number of passengers</span>
                                <span class="font-semibold">{{ $validated['passenger_count'] }}</span>
                            </div>
                            <hr class="my-3">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total Price</span>
                                <span>${{ number_format($totalPrice, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between">
                        <a href="{{ route('flights.show', $flight) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Back
                        </a>
                        <form action="{{ route('booking.create-draft') }}" method="POST">
                            @csrf
                            <input type="hidden" name="flight_id" value="{{ $validated['flight_id'] }}">
                            <input type="hidden" name="fare_class_id" value="{{ $validated['fare_class_id'] }}">
                            <input type="hidden" name="passenger_count" value="{{ $validated['passenger_count'] }}">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                                Continue to Passenger Details
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
