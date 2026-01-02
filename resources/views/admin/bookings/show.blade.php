<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Booking Details: {{ $booking->booking_reference }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Booking Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Booking Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">PNR</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $booking->booking_reference }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Contact Name</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $booking->contact_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Contact Email</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $booking->contact_email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Contact Phone</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $booking->contact_phone ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Booked On</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $booking->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flight Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Flight Details</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Flight Number</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $booking->flight->flight_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Route</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Departure</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $booking->flight->departure_time->format('M d, Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Fare Class</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $booking->fareClass->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Seats</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $booking->seat_count }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Price</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($booking->total_price, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Passengers -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Passengers</h3>
                    <div class="space-y-3">
                        @foreach($booking->passengers as $passenger)
                            <div class="border dark:border-gray-700 rounded p-3">
                                <div class="flex justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $passenger->full_name }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $passenger->passenger_type }} • DOB: {{ $passenger->date_of_birth->format('M d, Y') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Seat</p>
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $passenger->seat->seat_number ?? 'Not Assigned' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Add-ons -->
            @if($booking->addOns->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Add-ons</h3>
                    <div class="space-y-2">
                        @foreach($booking->addOns as $addOn)
                            <div class="flex justify-between border-b dark:border-gray-700 pb-2">
                                <span class="text-gray-900 dark:text-gray-100">{{ $addOn->addOn->name }} x{{ $addOn->quantity }}</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($addOn->price * $addOn->quantity, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Admin Actions -->
            @if($booking->status !== 'cancelled')
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Admin Actions</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <button onclick="showCancelModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                            Cancel Booking
                        </button>
                        <button onclick="showRebookModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                            Rebook Flight
                        </button>
                        @if($booking->status !== 'confirmed')
                        <button onclick="showMarkPaidModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                            Mark as Paid
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Activity Log -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Activity Log</h3>
                    <div class="space-y-3">
                        @forelse($booking->logs as $log)
                            <div class="border-l-4 border-blue-500 pl-4 py-2">
                                <div class="flex justify-between">
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($log->action) }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $log->created_at->format('M d, Y H:i') }}</p>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $log->description }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-500">By: {{ $log->user->name ?? 'System' }} ({{ $log->ip_address }})</p>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">No activity logs yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Cancel Booking</h3>
            <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST">
                @csrf
                <textarea name="reason" rows="4" required placeholder="Reason for cancellation..."
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 mb-3"></textarea>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Confirm</button>
                    <button type="button" onclick="closeCancelModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rebook Modal -->
    <div id="rebookModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Rebook to New Flight</h3>
            <form action="{{ route('admin.bookings.rebook', $booking) }}" method="POST">
                @csrf
                <input type="number" name="new_flight_id" placeholder="New Flight ID" required
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 mb-3">
                <textarea name="reason" rows="3" required placeholder="Reason for rebooking..."
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 mb-3"></textarea>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Confirm</button>
                    <button type="button" onclick="closeRebookModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mark Paid Modal -->
    <div id="markPaidModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Mark as Paid</h3>
            <form action="{{ route('admin.bookings.mark-paid', $booking) }}" method="POST">
                @csrf
                <textarea name="reason" rows="3" required placeholder="Reason (e.g., payment received via bank transfer)..."
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 mb-3"></textarea>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Confirm</button>
                    <button type="button" onclick="closeMarkPaidModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showCancelModal() { document.getElementById('cancelModal').classList.remove('hidden'); }
        function closeCancelModal() { document.getElementById('cancelModal').classList.add('hidden'); }
        function showRebookModal() { document.getElementById('rebookModal').classList.remove('hidden'); }
        function closeRebookModal() { document.getElementById('rebookModal').classList.add('hidden'); }
        function showMarkPaidModal() { document.getElementById('markPaidModal').classList.remove('hidden'); }
        function closeMarkPaidModal() { document.getElementById('markPaidModal').classList.add('hidden'); }
    </script>
</x-app-layout>
