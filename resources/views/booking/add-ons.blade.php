<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Services - {{ $booking->booking_reference }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-6">Enhance Your Trip</h3>

                    <form method="POST" action="{{ route('booking.add-ons.store', $booking) }}">
                        @csrf

                        <!-- Extra Baggage -->
                        <div class="mb-6 p-4 border rounded-lg">
                            <div class="flex items-start">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">Extra Baggage</h4>
                                    <p class="text-sm text-gray-600 mt-1">Add checked baggage (23kg per piece)</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="font-semibold text-blue-600">₱1,500</span>
                                    <select name="add_ons[baggage][quantity]" class="rounded border-gray-300">
                                        <option value="0">None</option>
                                        @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="add_ons[baggage][type]" value="baggage">
                            <input type="hidden" name="add_ons[baggage][price]" value="1500">
                        </div>

                        <!-- Meal Upgrade -->
                        <div class="mb-6 p-4 border rounded-lg">
                            <div class="flex items-start">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">In-Flight Meal</h4>
                                    <p class="text-sm text-gray-600 mt-1">Pre-order your meal selection</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="font-semibold text-blue-600">₱800</span>
                                    <select name="add_ons[meal][quantity]" class="rounded border-gray-300">
                                        <option value="0">None</option>
                                        @foreach($booking->passengers as $index => $passenger)
                                            <option value="{{ $index + 1 }}">{{ $index + 1 }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="add_ons[meal][type]" value="meal">
                            <input type="hidden" name="add_ons[meal][price]" value="800">
                        </div>

                        <!-- Priority Boarding -->
                        <div class="mb-6 p-4 border rounded-lg">
                            <div class="flex items-start">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">Priority Boarding</h4>
                                    <p class="text-sm text-gray-600 mt-1">Board the aircraft first</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="font-semibold text-blue-600">₱500</span>
                                    <select name="add_ons[priority_boarding][quantity]" class="rounded border-gray-300">
                                        <option value="0">None</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="add_ons[priority_boarding][type]" value="priority_boarding">
                            <input type="hidden" name="add_ons[priority_boarding][price]" value="500">
                        </div>

                        <!-- Current Add-ons Summary -->
                        @if($booking->addOns->count() > 0)
                            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                                <h4 class="font-semibold mb-3">Current Add-ons</h4>
                                @foreach($booking->addOns as $addOn)
                                    <div class="flex justify-between text-sm mb-2">
                                        <span>{{ ucfirst(str_replace('_', ' ', $addOn->type)) }} (x{{ $addOn->quantity }})</span>
                                        <span class="font-semibold">₱{{ number_format($addOn->price * $addOn->quantity, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex gap-4">
                            <a href="{{ route('booking.show', $booking) }}" 
                               class="flex-1 text-center px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg">
                                Skip
                            </a>
                            <button type="submit" 
                                    class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                                Save Add-ons
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
