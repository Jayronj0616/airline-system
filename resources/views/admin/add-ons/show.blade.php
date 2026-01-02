<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Add-on: {{ $addOn->name }}
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

            <!-- Add-on Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Add-on Details</h3>
                        <a href="{{ route('admin.add-ons.edit', $addOn) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                            Edit
                        </a>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Name</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $addOn->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Code</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $addOn->code }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Type</p>
                            <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                                {{ ucfirst(str_replace('_', ' ', $addOn->type)) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Base Price</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($addOn->base_price, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Max Quantity</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $addOn->max_quantity }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $addOn->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $addOn->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        @if($addOn->description)
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Description</p>
                            <p class="text-gray-900 dark:text-gray-100">{{ $addOn->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Availability Rules -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Availability Rules</h3>
                        <button onclick="showAddAvailabilityModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                            Add Rule
                        </button>
                    </div>

                    @if($addOn->availability->count() === 0)
                        <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-400 p-4 mb-4">
                            <p class="text-sm text-blue-700 dark:text-blue-200">
                                No specific rules defined. This add-on is available for all routes and fare classes.
                            </p>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Route</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fare Class</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($addOn->availability as $rule)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            @if($rule->route_origin && $rule->route_destination)
                                                {{ $rule->route_origin }} → {{ $rule->route_destination }}
                                            @else
                                                All Routes
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $rule->fareClass->name ?? 'All Classes' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            ₱{{ number_format($rule->price_override ?? $addOn->base_price, 2) }}
                                            @if($rule->price_override)
                                                <span class="text-xs text-gray-500">(Override)</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form action="{{ route('admin.add-ons.toggle-availability', $rule) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $rule->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $rule->is_available ? 'Available' : 'Disabled' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form action="{{ route('admin.add-ons.remove-availability', $rule) }}" method="POST" class="inline" onsubmit="return confirm('Remove this rule?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No availability rules defined.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Availability Modal -->
    <div id="addAvailabilityModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Add Availability Rule</h3>
            <form action="{{ route('admin.add-ons.add-availability', $addOn) }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Origin (Optional)</label>
                    <input type="text" name="route_origin" maxlength="3" placeholder="e.g., MNL"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 uppercase">
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Destination (Optional)</label>
                    <input type="text" name="route_destination" maxlength="3" placeholder="e.g., CEB"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 uppercase">
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fare Class (Optional)</label>
                    <select name="fare_class_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        <option value="">All Classes</option>
                        @foreach($fareClasses as $fareClass)
                            <option value="{{ $fareClass->id }}">{{ $fareClass->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price Override (Optional)</label>
                    <input type="number" step="0.01" name="price_override" min="0" placeholder="Leave blank for base price"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Add</button>
                    <button type="button" onclick="closeAddAvailabilityModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddAvailabilityModal() { document.getElementById('addAvailabilityModal').classList.remove('hidden'); }
        function closeAddAvailabilityModal() { document.getElementById('addAvailabilityModal').classList.add('hidden'); }
    </script>
</x-app-layout>
