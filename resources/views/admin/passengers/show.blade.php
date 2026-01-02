<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Passenger Details: {{ $passenger->full_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Passenger Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Passenger Information</h3>
                        <a href="{{ route('admin.passengers.edit', $passenger) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                            Correct Typos
                        </a>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Full Name</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $passenger->full_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Email</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $passenger->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Phone</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $passenger->phone ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Date of Birth</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $passenger->date_of_birth->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Gender</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($passenger->gender ?? 'N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Nationality</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $passenger->nationality ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Passport Number</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $passenger->passport_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Seat</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $passenger->seat->seat_number ?? 'Not Assigned' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Booking Details</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">PNR</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                <a href="{{ route('admin.bookings.show', $passenger->booking) }}" class="text-blue-600 hover:text-blue-900">
                                    {{ $passenger->booking->booking_reference }}
                                </a>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Flight</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $passenger->booking->flight->flight_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Route</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ $passenger->booking->flight->origin }} → {{ $passenger->booking->flight->destination }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Departure</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ $passenger->booking->flight->departure_time->format('M d, Y H:i') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Fare Class</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $passenger->booking->fareClass->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Booking Status</p>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($passenger->booking->status === 'confirmed') bg-green-100 text-green-800
                                @elseif($passenger->booking->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($passenger->booking->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($passenger->booking->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit History -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Edit History</h3>
                    <div class="space-y-3">
                        @forelse($passenger->editLogs as $log)
                            <div class="border-l-4 border-yellow-500 pl-4 py-2">
                                <div class="flex justify-between">
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $log->field_changed }} corrected</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $log->created_at->format('M d, Y H:i') }}</p>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="line-through">{{ $log->old_value }}</span> → <span class="font-semibold">{{ $log->new_value }}</span>
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Reason: {{ $log->reason }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-500">By: {{ $log->user->name ?? 'System' }} ({{ $log->ip_address }})</p>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">No edits made to this passenger record.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Warning Notice -->
            <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 dark:text-yellow-200">
                            <strong>Protected Fields:</strong> Date of birth, gender, nationality, and passport number cannot be edited.
                            Only minor typo corrections are allowed for name, email, and phone fields.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
