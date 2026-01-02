<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Seat Map: {{ $flight->flight_number }} ({{ $flight->origin }} → {{ $flight->destination }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Stats -->
            <div class="grid grid-cols-6 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-green-100 dark:bg-green-900 p-4 rounded-lg shadow">
                    <div class="text-sm text-green-700 dark:text-green-300">Available</div>
                    <div class="text-2xl font-bold text-green-900 dark:text-green-100">{{ $stats['available'] }}</div>
                </div>
                <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-lg shadow">
                    <div class="text-sm text-blue-700 dark:text-blue-300">Booked</div>
                    <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $stats['booked'] }}</div>
                </div>
                <div class="bg-yellow-100 dark:bg-yellow-900 p-4 rounded-lg shadow">
                    <div class="text-sm text-yellow-700 dark:text-yellow-300">Held</div>
                    <div class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ $stats['held'] }}</div>
                </div>
                <div class="bg-orange-100 dark:bg-orange-900 p-4 rounded-lg shadow">
                    <div class="text-sm text-orange-700 dark:text-orange-300">Crew</div>
                    <div class="text-2xl font-bold text-orange-900 dark:text-orange-100">{{ $stats['blocked_crew'] }}</div>
                </div>
                <div class="bg-red-100 dark:bg-red-900 p-4 rounded-lg shadow">
                    <div class="text-sm text-red-700 dark:text-red-300">Maintenance</div>
                    <div class="text-2xl font-bold text-red-900 dark:text-red-100">{{ $stats['blocked_maintenance'] }}</div>
                </div>
            </div>

            <!-- Legend -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow mb-6">
                <h3 class="font-semibold mb-3 text-gray-900 dark:text-gray-100">Legend</h3>
                <div class="flex flex-wrap gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-green-200 border border-green-400 rounded"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Available</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-blue-200 border border-blue-400 rounded"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Booked</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-yellow-200 border border-yellow-400 rounded"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Held</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-orange-200 border border-orange-400 rounded"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Blocked (Crew)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-red-200 border border-red-400 rounded"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Blocked (Maintenance)</span>
                    </div>
                </div>
            </div>

            <!-- Seat Map by Fare Class -->
            @foreach($seatMap as $className => $seats)
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6">
                    <h3 class="font-semibold text-lg mb-4 text-gray-900 dark:text-gray-100">{{ $className }}</h3>
                    
                    <div class="grid grid-cols-10 gap-2">
                        @foreach($seats as $seat)
                            <div class="relative group">
                                <button 
                                    onclick="showSeatModal({{ $seat->id }}, '{{ $seat->seat_number }}', '{{ $seat->status }}')"
                                    class="w-full aspect-square rounded border-2 text-xs font-semibold flex items-center justify-center
                                        @if($seat->status === 'available') bg-green-200 border-green-400 hover:bg-green-300
                                        @elseif($seat->status === 'booked') bg-blue-200 border-blue-400 cursor-not-allowed
                                        @elseif($seat->status === 'held') bg-yellow-200 border-yellow-400 hover:bg-yellow-300
                                        @elseif($seat->status === 'blocked_crew') bg-orange-200 border-orange-400 hover:bg-orange-300
                                        @elseif($seat->status === 'blocked_maintenance') bg-red-200 border-red-400 hover:bg-red-300
                                        @endif">
                                    {{ $seat->seat_number }}
                                </button>
                                
                                @if($seat->isBlocked())
                                    <div class="absolute hidden group-hover:block bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded shadow-lg w-48 z-10">
                                        <div class="font-semibold">{{ ucfirst(str_replace('blocked_', '', $seat->status)) }}</div>
                                        @if($seat->block_reason)
                                            <div class="mt-1">{{ $seat->block_reason }}</div>
                                        @endif
                                        @if($seat->blockedBy)
                                            <div class="mt-1 text-gray-300">By: {{ $seat->blockedBy->name }}</div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Seat Action Modal -->
    <div id="seatModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Seat <span id="modalSeatNumber"></span></h3>
            
            <div id="availableActions">
                <form id="blockForm" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" id="seatId" name="seat_id">
                    
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Block Type</label>
                    <select name="block_type" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 mb-3">
                        <option value="crew">Crew</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                    
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason (Optional)</label>
                    <textarea name="reason" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 mb-3"></textarea>
                    
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                        Block Seat
                    </button>
                </form>
            </div>

            <div id="blockedActions" class="hidden">
                <form id="releaseForm" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                        Release Seat
                    </button>
                </form>
            </div>

            <button onclick="closeSeatModal()" class="mt-3 w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Cancel
            </button>
        </div>
    </div>

    <script>
        function showSeatModal(seatId, seatNumber, status) {
            if (status === 'booked') return;
            
            document.getElementById('modalSeatNumber').textContent = seatNumber;
            document.getElementById('seatId').value = seatId;
            
            const blockForm = document.getElementById('blockForm');
            const releaseForm = document.getElementById('releaseForm');
            
            blockForm.action = `/admin/seats/${seatId}/block`;
            releaseForm.action = `/admin/seats/${seatId}/release`;
            
            if (status === 'blocked_crew' || status === 'blocked_maintenance') {
                document.getElementById('availableActions').classList.add('hidden');
                document.getElementById('blockedActions').classList.remove('hidden');
            } else {
                document.getElementById('availableActions').classList.remove('hidden');
                document.getElementById('blockedActions').classList.add('hidden');
            }
            
            document.getElementById('seatModal').classList.remove('hidden');
        }

        function closeSeatModal() {
            document.getElementById('seatModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
