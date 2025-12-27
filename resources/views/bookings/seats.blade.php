<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Select Your Seats') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Booking Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Flight Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-600">Flight</p>
                            <p class="font-semibold">{{ $booking->flight->flight_number }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Route</p>
                            <p class="font-semibold">{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Passengers</p>
                            <p class="font-semibold">{{ $booking->seat_count }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Class</p>
                            <p class="font-semibold">{{ $booking->fareClass->name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seat Legend -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Legend</h3>
                    <div class="flex flex-wrap gap-6">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-green-500 rounded"></div>
                            <span class="text-sm">Available</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-blue-500 rounded"></div>
                            <span class="text-sm">Selected</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-gray-400 rounded"></div>
                            <span class="text-sm">Occupied</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seat Map -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Select {{ $booking->seat_count }} Seat(s)</h3>
                    
                    <div class="max-w-2xl mx-auto">
                        <!-- Cockpit -->
                        <div class="text-center mb-8">
                            <div class="inline-block bg-gray-200 px-8 py-2 rounded-t-full">
                                <span class="text-sm font-semibold text-gray-600">✈ COCKPIT</span>
                            </div>
                        </div>

                        <!-- Seats -->
                        <div class="space-y-3" id="seat-map">
                            @php
                                $rows = [];
                                foreach ($seats as $seat) {
                                    preg_match('/(\d+)([A-Z])/', $seat->seat_number, $matches);
                                    if (count($matches) === 3) {
                                        $row = $matches[1];
                                        $col = $matches[2];
                                        $rows[$row][$col] = $seat;
                                    }
                                }
                                ksort($rows);
                            @endphp

                            @foreach($rows as $rowNumber => $rowSeats)
                                <div class="flex justify-center items-center gap-2">
                                    <!-- Row Number -->
                                    <div class="w-8 text-center text-sm font-semibold text-gray-600">{{ $rowNumber }}</div>
                                    
                                    <!-- Seats A-C -->
                                    <div class="flex gap-2">
                                        @foreach(['A', 'B', 'C'] as $col)
                                            @php $seat = $rowSeats[$col] ?? null; @endphp
                                            @if($seat)
                                                <button 
                                                    type="button"
                                                    data-seat-id="{{ $seat->id }}"
                                                    data-seat-number="{{ $seat->seat_number }}"
                                                    class="seat-btn w-10 h-10 rounded {{ $seat->status === 'available' ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-400 cursor-not-allowed' }} text-white text-xs font-semibold transition"
                                                    {{ $seat->status !== 'available' ? 'disabled' : '' }}>
                                                    {{ $col }}
                                                </button>
                                            @else
                                                <div class="w-10 h-10"></div>
                                            @endif
                                        @endforeach
                                    </div>

                                    <!-- Aisle -->
                                    <div class="w-8"></div>

                                    <!-- Seats D-F -->
                                    <div class="flex gap-2">
                                        @foreach(['D', 'E', 'F'] as $col)
                                            @php $seat = $rowSeats[$col] ?? null; @endphp
                                            @if($seat)
                                                <button 
                                                    type="button"
                                                    data-seat-id="{{ $seat->id }}"
                                                    data-seat-number="{{ $seat->seat_number }}"
                                                    class="seat-btn w-10 h-10 rounded {{ $seat->status === 'available' ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-400 cursor-not-allowed' }} text-white text-xs font-semibold transition"
                                                    {{ $seat->status !== 'available' ? 'disabled' : '' }}>
                                                    {{ $col }}
                                                </button>
                                            @else
                                                <div class="w-10 h-10"></div>
                                            @endif
                                        @endforeach
                                    </div>

                                    <!-- Row Number -->
                                    <div class="w-8 text-center text-sm font-semibold text-gray-600">{{ $rowNumber }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selected Seats Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Selected Seats</h3>
                    <div id="selected-seats-display" class="text-gray-600">
                        No seats selected
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <form method="POST" action="{{ route('bookings.seats.store', $booking) }}" id="seat-form">
                @csrf
                <input type="hidden" name="seats" id="selected-seats-input">
                
                <div class="flex justify-between">
                    <a href="{{ route('bookings.passengers', $booking) }}" class="text-gray-600 hover:text-gray-900">
                        ← Back
                    </a>
                    <button type="submit" id="continue-btn" disabled class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold py-2 px-6 rounded-lg transition">
                        Continue to Payment →
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const maxSeats = {{ $booking->seat_count }};
        let selectedSeats = [];

        document.querySelectorAll('.seat-btn').forEach(btn => {
            if (!btn.disabled) {
                btn.addEventListener('click', function() {
                    const seatId = this.dataset.seatId;
                    const seatNumber = this.dataset.seatNumber;
                    
                    if (selectedSeats.includes(seatId)) {
                        // Deselect
                        selectedSeats = selectedSeats.filter(id => id !== seatId);
                        this.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                        this.classList.add('bg-green-500', 'hover:bg-green-600');
                    } else {
                        // Select
                        if (selectedSeats.length < maxSeats) {
                            selectedSeats.push(seatId);
                            this.classList.remove('bg-green-500', 'hover:bg-green-600');
                            this.classList.add('bg-blue-500', 'hover:bg-blue-600');
                        } else {
                            alert(`You can only select ${maxSeats} seat(s)`);
                        }
                    }
                    
                    updateDisplay();
                });
            }
        });

        function updateDisplay() {
            const display = document.getElementById('selected-seats-display');
            const input = document.getElementById('selected-seats-input');
            const continueBtn = document.getElementById('continue-btn');
            
            if (selectedSeats.length === 0) {
                display.textContent = 'No seats selected';
                continueBtn.disabled = true;
            } else {
                const seatNumbers = selectedSeats.map(id => {
                    const btn = document.querySelector(`[data-seat-id="${id}"]`);
                    return btn.dataset.seatNumber;
                });
                display.innerHTML = `<div class="flex flex-wrap gap-2">${seatNumbers.map(num => 
                    `<span class="bg-blue-100 text-blue-800 px-3 py-1 rounded font-semibold">${num}</span>`
                ).join('')}</div>`;
                
                continueBtn.disabled = selectedSeats.length !== maxSeats;
            }
            
            input.value = JSON.stringify(selectedSeats);
        }
    </script>
    @endpush
</x-app-layout>
