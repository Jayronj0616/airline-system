@if(auth()->check())
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Passenger Details
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
                        <p class="font-semibold text-red-800 mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-red-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('booking.passengers.store', $booking) }}" method="POST">
                        @csrf

                        <!-- Contact Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">Contact Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                    <input type="text" name="contact_name" value="{{ old('contact_name', auth()->user()->name) }}" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('contact_name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                    <input type="email" name="contact_email" value="{{ old('contact_email', auth()->user()->email) }}" required readonly
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100">
                                    @error('contact_email')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                                    <input type="text" name="contact_phone" value="{{ old('contact_phone', auth()->user()->phone ?? '') }}" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('contact_phone')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Passengers -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">Passenger Information</h3>
                            @for ($i = 0; $i < $booking->seat_count; $i++)
                                <div class="mb-6 p-4 border rounded-lg">
                                    <h4 class="font-semibold mb-4">Passenger {{ $i + 1 }}</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                                            <input type="text" name="passengers[{{ $i }}][first_name]" value="{{ old("passengers.{$i}.first_name") }}" required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error("passengers.{$i}.first_name")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                                            <input type="text" name="passengers[{{ $i }}][last_name]" value="{{ old("passengers.{$i}.last_name") }}" required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error("passengers.{$i}.last_name")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                                            <input type="date" name="passengers[{{ $i }}][date_of_birth]" value="{{ old("passengers.{$i}.date_of_birth") }}" required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error("passengers.{$i}.date_of_birth")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                                            <select name="passengers[{{ $i }}][gender]" required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Select Gender</option>
                                                <option value="male" {{ old("passengers.{$i}.gender") == 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ old("passengers.{$i}.gender") == 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="other" {{ old("passengers.{$i}.gender") == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                            @error("passengers.{$i}.gender")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Nationality (ISO Code) *</label>
                                            <select name="passengers[{{ $i }}][nationality]" required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Select Nationality</option>
                                                <option value="PHL" selected>Philippines</option>
                                                <option value="USA">United States</option>
                                                <option value="CHN">China</option>
                                                <option value="JPN">Japan</option>
                                                <option value="KOR">Korea, South</option>
                                                <option value="SGP">Singapore</option>
                                                <option value="MYS">Malaysia</option>
                                                <option value="THA">Thailand</option>
                                                <option value="VNM">Vietnam</option>
                                                <option value="IDN">Indonesia</option>
                                                <option value="GBR">United Kingdom</option>
                                                <option value="CAN">Canada</option>
                                                <option value="AUS">Australia</option>
                                                <option value="NZL">New Zealand</option>
                                            </select>
                                            @error("passengers.{$i}.nationality")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Passport Number *</label>
                                            <input type="text" name="passengers[{{ $i }}][passport_number]" value="{{ old("passengers.{$i}.passport_number") }}" required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error("passengers.{$i}.passport_number")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <!-- Summary -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="font-semibold mb-2">Booking Summary</h3>
                            <div class="flex justify-between">
                                <span>Flight: {{ $booking->flight->flight_number }}</span>
                                <span>{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Fare Class: {{ $booking->fareClass->name }}</span>
                                <span>{{ $booking->seat_count }} passenger(s)</span>
                            </div>
                            <div class="flex justify-between font-bold text-lg mt-2">
                                <span>Total:</span>
                                <span>₱{{ number_format($booking->total_price, 2) }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-between">
                            <a href="{{ route('flights.show', $booking->flight) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                                Continue to Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
@else
<x-public-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
                        <p class="font-semibold text-red-800 mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-red-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('booking.passengers.store', $booking) }}" method="POST">
                        @csrf

                        <!-- Contact Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">Contact Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                    <input type="text" name="contact_name" value="{{ old('contact_name') }}" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('contact_name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                    <input type="email" name="contact_email" value="{{ old('contact_email') }}" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('contact_email')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                                    <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('contact_phone')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Passengers -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">Passenger Information</h3>
                            @for ($i = 0; $i < $booking->seat_count; $i++)
                                <div class="mb-6 p-4 border rounded-lg">
                                    <h4 class="font-semibold mb-4">Passenger {{ $i + 1 }}</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                                            <input type="text" name="passengers[{{ $i }}][first_name]" value="{{ old("passengers.{$i}.first_name") }}" required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error("passengers.{$i}.first_name")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                                            <input type="text" name="passengers[{{ $i }}][last_name]" value="{{ old("passengers.{$i}.last_name") }}" required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error("passengers.{$i}.last_name")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                                            <input type="date" name="passengers[{{ $i }}][date_of_birth]" value="{{ old("passengers.{$i}.date_of_birth") }}" required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error("passengers.{$i}.date_of_birth")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                                            <select name="passengers[{{ $i }}][gender]" required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Select Gender</option>
                                                <option value="male" {{ old("passengers.{$i}.gender") == 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ old("passengers.{$i}.gender") == 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="other" {{ old("passengers.{$i}.gender") == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                            @error("passengers.{$i}.gender")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Nationality (ISO Code) *</label>
                                            <select name="passengers[{{ $i }}][nationality]" required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Select Nationality</option>
                                                <option value="PHL" selected>Philippines</option>
                                                <option value="USA">United States</option>
                                                <option value="CHN">China</option>
                                                <option value="JPN">Japan</option>
                                                <option value="KOR">Korea, South</option>
                                                <option value="SGP">Singapore</option>
                                                <option value="MYS">Malaysia</option>
                                                <option value="THA">Thailand</option>
                                                <option value="VNM">Vietnam</option>
                                                <option value="IDN">Indonesia</option>
                                                <option value="GBR">United Kingdom</option>
                                                <option value="CAN">Canada</option>
                                                <option value="AUS">Australia</option>
                                                <option value="NZL">New Zealand</option>
                                            </select>
                                            @error("passengers.{$i}.nationality")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Passport Number *</label>
                                            <input type="text" name="passengers[{{ $i }}][passport_number]" value="{{ old("passengers.{$i}.passport_number") }}" required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error("passengers.{$i}.passport_number")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <!-- Summary -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="font-semibold mb-2">Booking Summary</h3>
                            <div class="flex justify-between">
                                <span>Flight: {{ $booking->flight->flight_number }}</span>
                                <span>{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Fare Class: {{ $booking->fareClass->name }}</span>
                                <span>{{ $booking->seat_count }} passenger(s)</span>
                            </div>
                            <div class="flex justify-between font-bold text-lg mt-2">
                                <span>Total:</span>
                                <span>₱{{ number_format($booking->total_price, 2) }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-between">
                            <a href="{{ route('flights.show', $booking->flight) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                                Continue to Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
@endif
