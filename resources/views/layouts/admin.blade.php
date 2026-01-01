<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Admin</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen">
            <!-- Admin Navigation -->
            @include('layouts.admin-navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-gradient-to-r from-purple-600 to-blue-600 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- SweetAlert2 Flash Messages -->
        @if (session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: '{{ session('success') }}',
                        confirmButtonColor: '#2563eb'
                    });
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: '{{ session('error') }}',
                        confirmButtonColor: '#2563eb'
                    });
                });
            </script>
        @endif

        @if (session('warning'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: '{{ session('warning') }}',
                        confirmButtonColor: '#2563eb'
                    });
                });
            </script>
        @endif

        @if (session('info'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'info',
                        title: 'Info',
                        text: '{{ session('info') }}',
                        confirmButtonColor: '#2563eb'
                    });
                });
            </script>
        @endif

        @stack('scripts')
    </body>
</html>
