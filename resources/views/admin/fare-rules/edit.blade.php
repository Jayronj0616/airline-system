<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Fare Rules') }} - {{ $fareClass->name }}
            </h2>
            <a href="{{ route('admin.fare-rules.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to All Rules
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    <form method="POST" action="{{ route('admin.fare-rules.update', $fareClass) }}" id="fareRulesForm">
                        @csrf
                        @method('PATCH')

                        <!-- Basic Rules Card -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Basic Rules</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Refundable -->
                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="is_refundable" 
                                                   value="1"
                                                   {{ old('is_refundable', $fareClass->fareRule->is_refundable) ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm font-medium text-gray-700">Refundable</span>
                                        </label>
                                    </div>

                                    <!-- Refund Fee Percentage -->
                                    <div>
                                        <label for="refund_fee_percentage" class="block text-sm font-medium text-gray-700 mb-1">
                                            Refund Fee (%)
                                        </label>
                                        <input type="number" 
                                               name="refund_fee_percentage" 
                                               id="refund_fee_percentage"
                                               value="{{ old('refund_fee_percentage', $fareClass->fareRule->refund_fee_percentage) }}"
                                               min="0" 
                                               max="100" 
                                               step="0.01"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>

                                    <!-- Change Fee -->
                                    <div>
                                        <label for="change_fee" class="block text-sm font-medium text-gray-700 mb-1">
                                            Change Fee (₱)
                                        </label>
                                        <input type="number" 
                                               name="change_fee" 
                                               id="change_fee"
                                               value="{{ old('change_fee', $fareClass->fareRule->change_fee) }}"
                                               min="0" 
                                               step="0.01"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>

                                    <!-- Cancellation Fee -->
                                    <div>
                                        <label for="cancellation_fee" class="block text-sm font-medium text-gray-700 mb-1">
                                            Cancellation Fee (₱)
                                        </label>
                                        <input type="number" 
                                               name="cancellation_fee" 
                                               id="cancellation_fee"
                                               value="{{ old('cancellation_fee', $fareClass->fareRule->cancellation_fee) }}"
                                               min="0" 
                                               step="0.01"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Baggage Rules Card -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Baggage Allowance</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Checked Bags -->
                                    <div>
                                        <label for="checked_bags_allowed" class="block text-sm font-medium text-gray-700 mb-1">
                                            Checked Bags Allowed
                                        </label>
                                        <input type="number" 
                                               name="checked_bags_allowed" 
                                               id="checked_bags_allowed"
                                               value="{{ old('checked_bags_allowed', $fareClass->fareRule->checked_bags_allowed) }}"
                                               min="0"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>

                                    <!-- Weight Limit -->
                                    <div>
                                        <label for="bag_weight_limit_kg" class="block text-sm font-medium text-gray-700 mb-1">
                                            Weight Limit per Bag (kg)
                                        </label>
                                        <input type="number" 
                                               name="bag_weight_limit_kg" 
                                               id="bag_weight_limit_kg"
                                               value="{{ old('bag_weight_limit_kg', $fareClass->fareRule->bag_weight_limit_kg) }}"
                                               min="0"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Seat Selection Card -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Seat Selection</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Free Seat Selection -->
                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="seat_selection_free" 
                                                   value="1"
                                                   {{ old('seat_selection_free', $fareClass->fareRule->seat_selection_free) ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm font-medium text-gray-700">Free Seat Selection</span>
                                        </label>
                                    </div>

                                    <!-- Seat Selection Fee -->
                                    <div>
                                        <label for="seat_selection_fee" class="block text-sm font-medium text-gray-700 mb-1">
                                            Seat Selection Fee (₱)
                                        </label>
                                        <input type="number" 
                                               name="seat_selection_fee" 
                                               id="seat_selection_fee"
                                               value="{{ old('seat_selection_fee', $fareClass->fareRule->seat_selection_fee) }}"
                                               min="0" 
                                               step="0.01"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Perks Card -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">Perks & Benefits</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               name="priority_boarding" 
                                               value="1"
                                               {{ old('priority_boarding', $fareClass->fareRule->priority_boarding) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm font-medium text-gray-700">Priority Boarding</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced JSON Rules Card -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold">Advanced JSON Rules</h3>
                                    <button type="button" 
                                            onclick="formatJSON()"
                                            class="text-sm text-indigo-600 hover:text-indigo-800">
                                        Format JSON
                                    </button>
                                </div>

                                <p class="text-sm text-gray-600 mb-3">
                                    Define custom rules using JSON format. See documentation for schema details.
                                </p>

                                <textarea name="rules_json" 
                                          id="rules_json" 
                                          rows="15"
                                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm"
                                          placeholder='{"refund_policy": {...}, "change_policy": {...}}'
                                >{{ old('rules_json', json_encode($fareClass->fareRule->rules_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>

                                <div id="json-error" class="mt-2 text-sm text-red-600 hidden"></div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end gap-4">
                            <a href="{{ route('admin.fare-rules.index') }}" 
                               class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                Save Rules
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Preview Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Preview</h3>
                            
                            <div class="space-y-3 text-sm">
                                <div class="p-3 bg-gray-50 rounded">
                                    <p class="font-semibold text-gray-900 mb-1">Refund</p>
                                    <p class="text-gray-700">{{ $ruleSummary['refundable'] }}</p>
                                </div>

                                <div class="p-3 bg-gray-50 rounded">
                                    <p class="font-semibold text-gray-900 mb-1">Changes</p>
                                    <p class="text-gray-700">{{ $ruleSummary['change_fee'] }}</p>
                                </div>

                                <div class="p-3 bg-gray-50 rounded">
                                    <p class="font-semibold text-gray-900 mb-1">Baggage</p>
                                    <p class="text-gray-700">{{ $ruleSummary['baggage'] }}</p>
                                </div>

                                <div class="p-3 bg-gray-50 rounded">
                                    <p class="font-semibold text-gray-900 mb-1">Seat Selection</p>
                                    <p class="text-gray-700">{{ $ruleSummary['seat_selection'] }}</p>
                                </div>

                                @if(!empty($ruleSummary['perks']))
                                    <div class="p-3 bg-gray-50 rounded">
                                        <p class="font-semibold text-gray-900 mb-1">Perks</p>
                                        <ul class="text-gray-700 space-y-1">
                                            @foreach($ruleSummary['perks'] as $perk)
                                                <li class="flex items-center">
                                                    <svg class="h-3 w-3 text-green-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    {{ ucwords(str_replace('_', ' ', $perk)) }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded text-xs text-blue-700">
                                <p class="font-semibold mb-1">Note:</p>
                                <p>Preview shows how rules will appear to customers during booking.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Format JSON button
        function formatJSON() {
            const textarea = document.getElementById('rules_json');
            const errorDiv = document.getElementById('json-error');
            
            try {
                const parsed = JSON.parse(textarea.value);
                textarea.value = JSON.stringify(parsed, null, 2);
                errorDiv.classList.add('hidden');
                errorDiv.textContent = '';
            } catch (e) {
                errorDiv.classList.remove('hidden');
                errorDiv.textContent = 'Invalid JSON: ' + e.message;
            }
        }

        // Validate JSON on form submit
        document.getElementById('fareRulesForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const textarea = document.getElementById('rules_json');
            const errorDiv = document.getElementById('json-error');
            
            if (textarea.value.trim()) {
                try {
                    JSON.parse(textarea.value);
                    errorDiv.classList.add('hidden');
                    errorDiv.textContent = '';
                    
                    // Show confirmation
                    Swal.fire({
                        title: 'Update Fare Rules?',
                        text: 'This will update the fare rules for this class and affect all future bookings.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#4f46e5',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, Update Rules',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            e.target.submit();
                        }
                    });
                } catch (error) {
                    errorDiv.classList.remove('hidden');
                    errorDiv.textContent = 'Invalid JSON: ' + error.message;
                    textarea.focus();
                }
            } else {
                Swal.fire({
                    title: 'Update Fare Rules?',
                    text: 'This will update the fare rules for this class and affect all future bookings.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4f46e5',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, Update Rules',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        e.target.submit();
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
