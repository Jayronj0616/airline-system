<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Airline System') }} - Compare and Book Flights</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <a href="{{ url('/') }}" class="flex items-center space-x-2 group">
                            <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                            </svg>
                            <span class="text-xl font-bold text-gray-900 group-hover:text-blue-600 transition">FlightHub</span>
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="flex items-center space-x-6">
                        <a href="{{ route('flights.search') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600 transition">
                            Flights
                        </a>
                        <a href="{{ route('manage-booking.retrieve') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600 transition">
                            Manage Booking
                        </a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600 transition">
                                My Bookings
                            </a>
                            @if(Auth::user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600 transition">
                                    Admin
                                </a>
                            @endif
                            <div class="relative group">
                                <button class="flex items-center space-x-2 text-sm font-medium text-gray-700 hover:text-blue-600 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>{{ Auth::user()->name }}</span>
                                </button>
                                <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block">
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600 transition">
                                Sign in
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded-lg transition text-sm shadow-sm">
                                    Sign up
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-300 mt-auto">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Company Info -->
                    <div>
                        <div class="flex items-center space-x-2 mb-4">
                            <svg class="w-7 h-7 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                            </svg>
                            <span class="text-xl font-bold text-white">FlightHub</span>
                        </div>
                        <p class="text-sm">Your trusted partner for finding the best flight deals worldwide.</p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-white font-semibold mb-4">Quick Links</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-white transition">About Us</a></li>
                            <li><a href="#" class="hover:text-white transition">Careers</a></li>
                            <li><a href="#" class="hover:text-white transition">Press</a></li>
                            <li><a href="#" class="hover:text-white transition">Blog</a></li>
                        </ul>
                    </div>

                    <!-- Support -->
                    <div>
                        <h3 class="text-white font-semibold mb-4">Support</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-white transition">Help Center</a></li>
                            <li><a href="#" class="hover:text-white transition">Contact Us</a></li>
                            <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                            <li><a href="#" class="hover:text-white transition">Terms of Service</a></li>
                        </ul>
                    </div>

                    <!-- Newsletter -->
                    <div>
                        <h3 class="text-white font-semibold mb-4">Stay Updated</h3>
                        <p class="text-sm mb-3">Get the latest deals and travel tips.</p>
                        <div class="flex">
                            <input type="email" placeholder="Your email" class="flex-1 px-3 py-2 rounded-l-lg text-gray-900 text-sm focus:outline-none">
                            <button class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-r-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm">
                    <p>&copy; {{ date('Y') }} FlightHub. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- SweetAlert for session messages -->
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '{{ session('success') }}',
            confirmButtonColor: '#2563eb'
        });
    </script>
    @endif
    
    @if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
            confirmButtonColor: '#2563eb'
        });
    </script>
    @endif
    
    @stack('scripts')
</body>
</html>
