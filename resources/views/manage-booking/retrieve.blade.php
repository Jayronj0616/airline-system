<x-public-layout>
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">Manage Your Booking</h1>
                <p class="text-gray-600 mb-8">Enter your booking reference and last name to view and manage your booking.</p>

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('manage-booking.show') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="booking_reference" class="block text-sm font-medium text-gray-700 mb-2">
                            Booking Reference
                        </label>
                        <input 
                            type="text" 
                            name="booking_reference" 
                            id="booking_reference" 
                            placeholder="ABC123XYZ"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase"
                            value="{{ old('booking_reference') }}"
                            required
                        >
                        @error('booking_reference')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Last Name
                        </label>
                        <input 
                            type="text" 
                            name="last_name" 
                            id="last_name" 
                            placeholder="Smith"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            value="{{ old('last_name') }}"
                            required
                        >
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200"
                    >
                        Retrieve Booking
                    </button>
                </form>

                <div class="mt-8 pt-8 border-t border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Need Help?</h2>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li>• Your booking reference can be found in your confirmation email</li>
                        <li>• Use the last name exactly as it appears on your booking</li>
                        <li>• For assistance, contact our support team</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
