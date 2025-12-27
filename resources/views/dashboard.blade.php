<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Search Flights Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Search Flights</h3>
                        <p class="text-gray-600 text-sm mb-4">Find and book your next flight with real-time pricing</p>
                        <a href="{{ route('flights.search') }}" 
                           class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold">
                            Start Searching →
                        </a>
                    </div>
                </div>

                <!-- My Bookings Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">My Bookings</h3>
                        <p class="text-gray-600 text-sm mb-4">View and manage your flight bookings</p>
                        <a href="{{ route('bookings.index') }}" 
                           class="inline-flex items-center text-green-600 hover:text-green-800 font-semibold">
                            View Bookings →
                        </a>
                    </div>
                </div>

                <!-- Profile Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">My Profile</h3>
                        <p class="text-gray-600 text-sm mb-4">Update your account information</p>
                        <a href="{{ route('profile.edit') }}" 
                           class="inline-flex items-center text-purple-600 hover:text-purple-800 font-semibold">
                            Edit Profile →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Welcome Message -->
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-2">Welcome back, {{ Auth::user()->name }}!</h3>
                    <p class="text-gray-600">
                        Ready to book your next adventure? Our dynamic pricing system ensures you get the best deals.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
