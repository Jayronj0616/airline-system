<x-public-layout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                    Reset your password
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                    Enter your email address and we'll send you a link to reset your password.
                </p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 p-4">
                    <p class="text-sm font-medium text-green-800 dark:text-green-400">{{ session('status') }}</p>
                </div>
            @endif

            <form class="mt-8 space-y-6 bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md" method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Email address
                    </label>
                    <input id="email" name="email" type="email" autocomplete="email" required 
                        value="{{ old('email') }}"
                        class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm" 
                        placeholder="you@example.com">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="text-sm">
                        <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                            Back to sign in
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Email Password Reset Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-public-layout>
