<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Fare Rules Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-blue-600 mt-0.5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-900">About Fare Rules</h3>
                        <p class="text-sm text-blue-700 mt-1">
                            Fare rules define the policies for each fare class: refunds, changes, baggage allowances, and more. 
                            These rules are automatically applied when customers book, cancel, or modify their reservations.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-6">Fare Classes</h3>

                    @if($fareClasses->isEmpty())
                        <p class="text-gray-500 text-center py-8">No fare classes found.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($fareClasses as $fareClass)
                                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h4 class="text-xl font-bold text-gray-900">{{ $fareClass->name }}</h4>
                                            <p class="text-sm text-gray-600">Code: {{ strtoupper($fareClass->code) }}</p>
                                        </div>
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                            {{ $fareClass->code }}
                                        </span>
                                    </div>

                                    @if($fareClass->description)
                                        <p class="text-sm text-gray-700 mb-4">{{ $fareClass->description }}</p>
                                    @endif

                                    @if($fareClass->fareRule)
                                        <div class="space-y-2 mb-4 text-sm">
                                            <div class="flex items-center">
                                                @if($fareClass->fareRule->is_refundable)
                                                    <svg class="h-4 w-4 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span class="text-green-700">Refundable</span>
                                                @else
                                                    <svg class="h-4 w-4 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    <span class="text-red-700">Non-refundable</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center">
                                                <svg class="h-4 w-4 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                </svg>
                                                <span class="text-gray-700">
                                                    @if($fareClass->fareRule->change_fee > 0)
                                                        ₱{{ number_format($fareClass->fareRule->change_fee, 2) }} change fee
                                                    @else
                                                        Free changes
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex items-center">
                                                <svg class="h-4 w-4 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                                <span class="text-gray-700">{{ $fareClass->fareRule->checked_bags_allowed }} checked bag(s)</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-sm">
                                            <p class="text-yellow-800">No rules configured</p>
                                        </div>
                                    @endif

                                    <a href="{{ route('admin.fare-rules.edit', $fareClass) }}" 
                                       class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                                        Edit Rules
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Documentation Link -->
            <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-gray-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <p class="text-sm text-gray-700">
                        Need help? Check the 
                        <a href="{{ asset('FARE_RULES.md') }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                            Fare Rules Documentation
                        </a> for JSON schema and examples.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
