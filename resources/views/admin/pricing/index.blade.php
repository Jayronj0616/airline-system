<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Pricing Management') }}
            </h2>
            <form method="POST" action="{{ route('admin.pricing.recalculate-all') }}" id="recalculateAllForm">
                @csrf
                <button type="button" 
                        onclick="confirmRecalculateAll()"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Recalculate All Prices
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Upcoming Flights</h3>
                    
                    @if($flights->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">No upcoming flights.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Flight
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Route
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Departure
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Base Fares
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Load Factor
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($flights as $flight)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $flight->flight_number }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $flight->aircraft->model ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $flight->origin }} → {{ $flight->destination }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $flight->departure_time->format('M d, Y h:i A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-xs space-y-1">
                                                    <div><span class="text-gray-500 dark:text-gray-400">Y:</span> <span class="text-gray-900 dark:text-gray-100">₱{{ number_format($flight->base_price_economy, 2) }}</span></div>
                                                    <div><span class="text-gray-500 dark:text-gray-400">J:</span> <span class="text-gray-900 dark:text-gray-100">₱{{ number_format($flight->base_price_business, 2) }}</span></div>
                                                    <div><span class="text-gray-500 dark:text-gray-400">F:</span> <span class="text-gray-900 dark:text-gray-100">₱{{ number_format($flight->base_price_first, 2) }}</span></div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-2">
                                                        <div class="bg-indigo-600 dark:bg-indigo-500 h-2 rounded-full" style="width: {{ $flight->load_factor }}%"></div>
                                                    </div>
                                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $flight->load_factor }}%</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <div class="flex items-center space-x-3">
                                                    <!-- Edit Icon -->
                                                    <button type="button" 
                                                            onclick="openEditModal({{ $flight->id }}, '{{ $flight->flight_number }}', {{ $flight->base_price_economy }}, {{ $flight->base_price_business }}, {{ $flight->base_price_first }})"
                                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                            title="Edit Fares">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>
                                                    
                                                    <!-- Recalculate Icon -->
                                                    <form method="POST" 
                                                          action="{{ route('admin.pricing.recalculate', $flight) }}" 
                                                          class="inline recalculate-form">
                                                        @csrf
                                                        <button type="button" 
                                                                onclick="confirmRecalculate(this.closest('form'))"
                                                                class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                                                title="Recalculate Prices">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $flights->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Pricing Modal -->
    <div id="editPricingModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Edit Base Fares - <span id="modalFlightNumber"></span></h3>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form method="POST" id="editPricingForm">
                @csrf
                @method('PATCH')

                <div class="space-y-4">
                    <!-- Economy Class -->
                    <div>
                        <label for="modal_base_price_economy" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Economy Class Base Fare (₱)
                        </label>
                        <input type="number" 
                               name="base_price_economy" 
                               id="modal_base_price_economy" 
                               step="0.01"
                               required
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Business Class -->
                    <div>
                        <label for="modal_base_price_business" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Business Class Base Fare (₱)
                        </label>
                        <input type="number" 
                               name="base_price_business" 
                               id="modal_base_price_business" 
                               step="0.01"
                               required
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- First Class -->
                    <div>
                        <label for="modal_base_price_first" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            First Class Base Fare (₱)
                        </label>
                        <input type="number" 
                               name="base_price_first" 
                               id="modal_base_price_first" 
                               step="0.01"
                               required
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4">
                        <p class="text-xs text-blue-800 dark:text-blue-300">
                            <strong>Pricing Formula:</strong> Final Price = Base Fare × Time Factor × Inventory Factor × Demand Factor
                        </p>
                        <p class="text-xs text-blue-700 dark:text-blue-400 mt-2">
                            After updating, prices will be automatically recalculated based on current time, inventory, and demand factors.
                        </p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" 
                            onclick="closeEditModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="button" 
                            onclick="confirmPriceUpdate()"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Update Fares
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function openEditModal(flightId, flightNumber, economy, business, first) {
            document.getElementById('modalFlightNumber').textContent = flightNumber;
            document.getElementById('modal_base_price_economy').value = economy;
            document.getElementById('modal_base_price_business').value = business;
            document.getElementById('modal_base_price_first').value = first;
            
            const form = document.getElementById('editPricingForm');
            form.action = `/admin/pricing/${flightId}`;
            
            document.getElementById('editPricingModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editPricingModal').classList.add('hidden');
        }

        function confirmPriceUpdate() {
            Swal.fire({
                title: 'Update Base Fares?',
                text: 'This will update the base fares and recalculate prices for all fare classes.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Update Fares',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('editPricingForm').submit();
                }
            });
        }

        function confirmRecalculate(form) {
            Swal.fire({
                title: 'Recalculate Prices?',
                text: 'This will recalculate prices for all fare classes based on current factors.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Recalculate',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        function confirmRecalculateAll() {
            Swal.fire({
                title: 'Recalculate All Prices?',
                text: 'This will recalculate prices for ALL future flights. This may take a moment.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Recalculate All',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('recalculateAllForm').submit();
                }
            });
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeEditModal();
            }
        });

        // Close modal on outside click
        document.getElementById('editPricingModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeEditModal();
            }
        });
    </script>
    @endpush
</x-app-layout>
