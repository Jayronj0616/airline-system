<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(Auth::user()->role === 'admin')
            <!-- Admin Dashboard Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Admin Dashboard Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 bg-blue-100 dark:bg-blue-900/50 rounded-lg mb-4">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Admin Dashboard</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">View system analytics and metrics</p>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold">
                            View Dashboard →
                        </a>
                    </div>
                </div>

                <!-- Pricing Management Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-900/50 rounded-lg mb-4">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Pricing Management</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Manage flight prices and algorithms</p>
                        <a href="{{ route('admin.pricing.index') }}" 
                           class="inline-flex items-center text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-semibold">
                            Manage Pricing →
                        </a>
                    </div>
                </div>

                <!-- Demand Analytics Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-100 dark:bg-purple-900/50 rounded-lg mb-4">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Demand Analytics</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">View demand patterns and insights</p>
                        <a href="{{ route('admin.demand.index') }}" 
                           class="inline-flex items-center text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-semibold">
                            View Analytics →
                        </a>
                    </div>
                </div>
            </div>
            @else
            <!-- Regular User Dashboard Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Search Flights Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 bg-blue-100 dark:bg-blue-900/50 rounded-lg mb-4">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Search Flights</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Find and book your next flight with real-time pricing</p>
                        <a href="{{ route('flights.search') }}" 
                           class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold">
                            Start Searching →
                        </a>
                    </div>
                </div>

                <!-- My Bookings Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-900/50 rounded-lg mb-4">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">My Bookings</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">View and manage your flight bookings</p>
                        <a href="{{ route('profile.booking-history') }}" 
                           class="inline-flex items-center text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-semibold">
                            View Bookings →
                        </a>
                    </div>
                </div>

                <!-- Profile Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-100 dark:bg-purple-900/50 rounded-lg mb-4">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">My Profile</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Update your account information</p>
                        <a href="{{ route('profile.edit') }}" 
                           class="inline-flex items-center text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-semibold">
                            Edit Profile →
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Welcome Message -->
            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-2">Welcome back, {{ Auth::user()->name }}!</h3>
                    @if(Auth::user()->role === 'admin')
                    <p class="text-gray-600 dark:text-gray-400">
                        Manage your airline system with powerful analytics and pricing tools.
                    </p>
                    @else
                    <p class="text-gray-600 dark:text-gray-400">
                        Ready to book your next adventure? Our dynamic pricing system ensures you get the best deals.
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
