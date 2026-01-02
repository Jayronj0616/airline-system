<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Correct Passenger Information: {{ $passenger->full_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Warning -->
            <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 dark:text-yellow-200">
                            <strong>Typo Corrections Only:</strong> You can only make minor corrections (max 3 characters or 20% change).
                            Identity fields like date of birth, gender, nationality, and passport cannot be changed.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.passengers.update', $passenger) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Editable Fields (Typos Only)</h3>

                        @foreach($editableFields as $field => $label)
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ $label }}
                                    <span class="text-xs text-gray-500">(Current: {{ $passenger->$field }})</span>
                                </label>
                                <input 
                                    type="{{ $field === 'email' ? 'email' : 'text' }}" 
                                    name="{{ $field }}" 
                                    value="{{ old($field, $passenger->$field) }}" 
                                    required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error($field) border-red-500 @enderror">
                                @error($field)
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason for Correction *</label>
                            <textarea 
                                name="reason" 
                                rows="4" 
                                required
                                placeholder="Explain why this correction is needed (e.g., 'Customer reported typo in name', 'Email address misspelled')..."
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('reason') border-red-500 @enderror">{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <hr class="my-6 border-gray-300 dark:border-gray-600">

                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Protected Identity Fields (Read-Only)</h3>

                        <div class="grid grid-cols-2 gap-4 mb-6">
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
                        </div>

                        <div class="flex items-center justify-between">
                            <a href="{{ route('admin.passengers.show', $passenger) }}" 
                                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Cancel
                            </a>
                            <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Save Corrections
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
