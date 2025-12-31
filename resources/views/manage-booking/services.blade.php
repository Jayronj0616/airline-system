<x-public-layout>
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">Add Services</h1>
                <p class="text-gray-600 mb-8">Booking Reference: <span class="font-semibold">{{ $booking->booking_reference }}</span></p>

                <form method="POST" action="{{ route('manage-booking.services.store') }}" x-data="servicesForm()">
                    @csrf
                    <input type="hidden" name="booking_reference" value="{{ $booking->booking_reference }}">
                    <input type="hidden" name="last_name" value="{{ $booking->passengers->first()->last_name }}">

                    <!-- Extra Baggage -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Extra Baggage</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($prices['baggage'] as $weight => $price)
                                <label class="flex items-center space-x-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" @change="toggleService('baggage', '{{ $weight }}', {{ $price }})" class="w-5 h-5 text-blue-600">
                                    <div class="flex-1">
                                        <p class="font-medium">{{ $weight }} Baggage</p>
                                        <p class="text-sm text-gray-600">₱{{ number_format($price, 2) }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Meals -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Meal Selection</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($prices['meal'] as $type => $price)
                                <label class="flex items-center space-x-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" @change="toggleService('meal', '{{ $type }}', {{ $price }})" class="w-5 h-5 text-blue-600">
                                    <div class="flex-1">
                                        <p class="font-medium">{{ ucwords(str_replace('_', ' ', $type)) }}</p>
                                        <p class="text-sm text-gray-600">₱{{ number_format($price, 2) }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Seat Upgrades -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Seat Upgrades</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($prices['seat_upgrade'] as $type => $price)
                                <label class="flex items-center space-x-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" @change="toggleService('seat_upgrade', '{{ $type }}', {{ $price }})" class="w-5 h-5 text-blue-600">
                                    <div class="flex-1">
                                        <p class="font-medium">{{ ucwords(str_replace('_', ' ', $type)) }}</p>
                                        <p class="text-sm text-gray-600">₱{{ number_format($price, 2) }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Other Services -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Other Services</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="flex items-center space-x-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="checkbox" @change="toggleService('insurance', 'Travel Insurance', {{ $prices['insurance'] }})" class="w-5 h-5 text-blue-600">
                                <div class="flex-1">
                                    <p class="font-medium">Travel Insurance</p>
                                    <p class="text-sm text-gray-600">₱{{ number_format($prices['insurance'], 2) }}</p>
                                </div>
                            </label>
                            <label class="flex items-center space-x-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="checkbox" @change="toggleService('priority_boarding', 'Priority Boarding', {{ $prices['priority_boarding'] }})" class="w-5 h-5 text-blue-600">
                                <div class="flex-1">
                                    <p class="font-medium">Priority Boarding</p>
                                    <p class="text-sm text-gray-600">₱{{ number_format($prices['priority_boarding'], 2) }}</p>
                                </div>
                            </label>
                            <label class="flex items-center space-x-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="checkbox" @change="toggleService('lounge_access', 'Lounge Access', {{ $prices['lounge_access'] }})" class="w-5 h-5 text-blue-600">
                                <div class="flex-1">
                                    <p class="font-medium">Lounge Access</p>
                                    <p class="text-sm text-gray-600">₱{{ number_format($prices['lounge_access'], 2) }}</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Hidden inputs for services -->
                    <template x-for="(service, index) in services" :key="index">
                        <div>
                            <input type="hidden" :name="'services[' + index + '][type]'" :value="service.type">
                            <input type="hidden" :name="'services[' + index + '][description]'" :value="service.description">
                            <input type="hidden" :name="'services[' + index + '][price]'" :value="service.price">
                            <input type="hidden" :name="'services[' + index + '][quantity]'" :value="service.quantity">
                        </div>
                    </template>

                    <!-- Total -->
                    <div class="border-t border-gray-200 pt-6 mt-6">
                        <div class="flex justify-between items-center text-xl font-bold">
                            <p>Total Additional Services:</p>
                            <p x-text="'₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></p>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex space-x-4 mt-8">
                        <a href="{{ route('manage-booking.show') }}?booking_reference={{ $booking->booking_reference }}&last_name={{ $booking->passengers->first()->last_name }}" 
                           class="flex-1 bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg hover:bg-gray-300 text-center">
                            Cancel
                        </a>
                        <button type="submit" class="flex-1 bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-blue-700">
                            Add Services
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function servicesForm() {
            return {
                services: [],
                total: 0,
                toggleService(type, description, price) {
                    const index = this.services.findIndex(s => s.type === type && s.description === description);
                    if (index >= 0) {
                        this.services.splice(index, 1);
                    } else {
                        this.services.push({ type, description, price, quantity: 1 });
                    }
                    this.calculateTotal();
                },
                calculateTotal() {
                    this.total = this.services.reduce((sum, service) => sum + (service.price * service.quantity), 0);
                }
            }
        }
    </script>
</x-public-layout>
