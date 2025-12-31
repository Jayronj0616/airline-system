<x-public-layout>
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">Edit Passenger Information</h1>
                <p class="text-gray-600 mb-8">Booking Reference: <span class="font-semibold">{{ $booking->booking_reference }}</span></p>

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('manage-booking.update-passengers') }}">
                    @csrf
                    <input type="hidden" name="booking_reference" value="{{ $booking->booking_reference }}">
                    <input type="hidden" name="last_name" value="{{ $booking->passengers->first()->last_name }}">

                    @foreach($booking->passengers as $index => $passenger)
                        <div class="mb-8 pb-8 @if(!$loop->last) border-b border-gray-200 @endif">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Passenger {{ $loop->iteration }}</h2>
                            
                            <input type="hidden" name="passengers[{{ $index }}][id]" value="{{ $passenger->id }}">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                    <input 
                                        type="text" 
                                        name="passengers[{{ $index }}][first_name]" 
                                        value="{{ old("passengers.$index.first_name", $passenger->first_name) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required
                                    >
                                    @error("passengers.$index.first_name")
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                    <input 
                                        type="text" 
                                        name="passengers[{{ $index }}][last_name]" 
                                        value="{{ old("passengers.$index.last_name", $passenger->last_name) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required
                                    >
                                    @error("passengers.$index.last_name")
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input 
                                        type="email" 
                                        name="passengers[{{ $index }}][email]" 
                                        value="{{ old("passengers.$index.email", $passenger->email) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required
                                    >
                                    @error("passengers.$index.email")
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                    <input 
                                        type="text" 
                                        name="passengers[{{ $index }}][phone]" 
                                        value="{{ old("passengers.$index.phone", $passenger->phone) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                    @error("passengers.$index.phone")
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Passport Number</label>
                                    <input 
                                        type="text" 
                                        name="passengers[{{ $index }}][passport_number]" 
                                        value="{{ old("passengers.$index.passport_number", $passenger->passport_number) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                    @error("passengers.$index.passport_number")
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <p class="text-sm text-gray-700">
                            <strong>Note:</strong> Name changes may require additional verification. Ensure all information matches your travel documents.
                        </p>
                    </div>

                    <div class="flex space-x-4">
                        <a href="{{ route('manage-booking.show') }}?booking_reference={{ $booking->booking_reference }}&last_name={{ $booking->passengers->first()->last_name }}" 
                           class="flex-1 bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg hover:bg-gray-300 text-center">
                            Cancel
                        </a>
                        <button type="submit" class="flex-1 bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-blue-700">
                            Update Information
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-public-layout>
