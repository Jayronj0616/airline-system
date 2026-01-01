<x-public-layout>
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">Edit Contact Information</h1>
                <p class="text-gray-600 mb-8">Booking Reference: <span class="font-semibold">{{ $booking->booking_reference }}</span></p>

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('manage-booking.update-contact') }}" id="updateContactForm">
                    @csrf
                    <input type="hidden" name="booking_reference" value="{{ $booking->booking_reference }}">
                    <input type="hidden" name="last_name" value="{{ $booking->passengers->first()->last_name }}">

                    <div class="space-y-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                value="{{ old('email', $booking->user->email) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Booking confirmations and updates will be sent to this email</p>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Phone Number
                            </label>
                            <input 
                                type="text" 
                                name="phone" 
                                id="phone" 
                                value="{{ old('phone', $booking->passengers->first()->phone) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="+63 XXX XXX XXXX"
                            >
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Optional: For flight updates and notifications</p>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
                        <p class="text-sm text-gray-700">
                            <strong>Important:</strong> Make sure your email address is correct. All booking communications will be sent to this address.
                        </p>
                    </div>

                    <div class="flex space-x-4 mt-8">
                        <a href="{{ route('manage-booking.show') }}?booking_reference={{ $booking->booking_reference }}&last_name={{ $booking->passengers->first()->last_name }}" 
                           class="flex-1 bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg hover:bg-gray-300 text-center">
                            Cancel
                        </a>
                        <button type="button" onclick="confirmUpdateContact()" class="flex-1 bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-blue-700">
                            Update Contact
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function confirmUpdateContact() {
            Swal.fire({
                title: 'Update Contact Information?',
                text: 'All booking communications will be sent to the new email address.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Update',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('updateContactForm').submit();
                }
            });
        }
    </script>
</x-public-layout>
