<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Select Your Seats
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Booking Info -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-semibold mb-2">Flight Details</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Flight:</span>
                                <span class="font-semibold">{{ $booking->flight->flight_number }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Route:</span>
                                <span class="font-semibold">{{ $booking->flight->origin }} → {{ $booking->flight->destination }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Fare Class:</span>
                                <span class="font-semibold">{{ $booking->fareClass->name }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Passengers:</span>
                                <span class="font-semibold">{{ $booking->passengers->count() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Current Passenger Selection -->
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold">Selecting seat for:</h3>
                                <p class="text-lg" id="current-passenger-name">{{ $booking->passengers[0]->first_name }} {{ $booking->passengers[0]->last_name }}</p>
                            </div>
                            <div class="text-sm">
                                <span id="current-passenger-index">1</span> of {{ $booking->passengers->count() }}
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('booking.store-seats', $booking) }}" method="POST" id="seat-form">
                        @csrf

                        <!-- Hidden inputs for selected seats -->
                        @foreach($booking->passengers as $index => $passenger)
                            <input type="hidden" name="seats[{{ $index }}]" id="seat-input-{{ $index }}" value="">
                        @endforeach

                        <!-- Seat Map Container -->
                        <div class="mb-6">
                            <div class="bg-gradient-to-b from-blue-100 to-white rounded-lg p-6">
                                <!-- Cockpit -->
                                <div class="text-center mb-6">
                                    <div class="inline-block bg-blue-600 text-white px-8 py-2 rounded-t-full font-semibold">
                                        ✈ COCKPIT
                                    </div>
                                </div>

                                <!-- Seat Grid -->
                                <div class="max-w-3xl mx-auto">
                                    @php
                                        $seats = $booking->flight->seats->groupBy(function($seat) {
                                            return substr($seat->seat_number, 0, -1); // Group by row number
                                        })->sortKeys();
                                    @endphp

                                    @foreach($seats as $row => $rowSeats)
                                        <div class="flex items-center justify-center mb-3">
                                            <!-- Row Number Left -->
                                            <div class="w-8 text-center text-sm font-semibold text-gray-600">
                                                {{ $row }}
                                            </div>

                                            <!-- Seats -->
                                            <div class="flex gap-2">
                                                @php
                                                    $sortedSeats = $rowSeats->sortBy(function($seat) {
                                                        return substr($seat->seat_number, -1);
                                                    });
                                                    $seatLetters = $sortedSeats->pluck('seat_number')->map(function($num) {
                                                        return substr($num, -1);
                                                    })->toArray();
                                                @endphp

                                                @foreach($sortedSeats as $index => $seat)
                                                    @php
                                                        $letter = substr($seat->seat_number, -1);
                                                        $isAvailable = $seat->fare_class_id == $booking->fare_class_id && !$seat->is_occupied;
                                                        $seatClass = 'seat-btn w-12 h-12 rounded-lg font-semibold text-sm transition-all duration-200 border-2 ';
                                                        
                                                        if ($isAvailable) {
                                                            $seatClass .= 'bg-green-100 border-green-400 text-green-700 hover:bg-green-200 cursor-pointer';
                                                        } else {
                                                            $seatClass .= 'bg-gray-200 border-gray-300 text-gray-400 cursor-not-allowed';
                                                        }
                                                    @endphp

                                                    <button 
                                                        type="button" 
                                                        class="{{ $seatClass }}" 
                                                        data-seat-id="{{ $seat->id }}"
                                                        data-seat-number="{{ $seat->seat_number }}"
                                                        @if(!$isAvailable) disabled @endif
                                                    >
                                                        {{ $letter }}
                                                    </button>

                                                    <!-- Aisle space after C -->
                                                    @if($letter == 'C' && in_array('D', $seatLetters))
                                                        <div class="w-8"></div>
                                                    @endif
                                                @endforeach
                                            </div>

                                            <!-- Row Number Right -->
                                            <div class="w-8 text-center text-sm font-semibold text-gray-600">
                                                {{ $row }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Exit -->
                                <div class="text-center mt-6">
                                    <div class="inline-block bg-red-600 text-white px-8 py-2 rounded-b-lg font-semibold">
                                        🚪 EXIT
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="font-semibold mb-3">Seat Legend</h4>
                            <div class="flex flex-wrap gap-4 text-sm">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-green-100 border-2 border-green-400 rounded-lg mr-2"></div>
                                    <span>Available</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-500 border-2 border-blue-700 rounded-lg mr-2"></div>
                                    <span>Your Selection</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-200 border-2 border-gray-300 rounded-lg mr-2"></div>
                                    <span>Occupied</span>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Seats Summary -->
                        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                            <h4 class="font-semibold mb-3">Selected Seats</h4>
                            <div id="selected-seats-summary" class="space-y-2 text-sm">
                                @foreach($booking->passengers as $index => $passenger)
                                    <div class="flex justify-between items-center">
                                        <span>{{ $passenger->first_name }} {{ $passenger->last_name }}:</span>
                                        <span class="font-semibold" id="summary-{{ $index }}">Not selected</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-between">
                            <a href="{{ route('booking.show', $booking) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Skip for Now
                            </a>
                            <button type="submit" id="submit-btn" disabled class="bg-gray-400 text-white font-bold py-2 px-6 rounded cursor-not-allowed">
                                Confirm Seats
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const passengers = @json($booking->passengers->values());
        let currentPassengerIndex = 0;
        let selectedSeats = {};

        function updateCurrentPassenger() {
            const passenger = passengers[currentPassengerIndex];
            document.getElementById('current-passenger-name').textContent = 
                `${passenger.first_name} ${passenger.last_name}`;
            document.getElementById('current-passenger-index').textContent = currentPassengerIndex + 1;
        }

        function updateSubmitButton() {
            const allSelected = Object.keys(selectedSeats).length === passengers.length;
            const submitBtn = document.getElementById('submit-btn');
            
            if (allSelected) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                submitBtn.classList.add('bg-blue-500', 'hover:bg-blue-700', 'cursor-pointer');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
                submitBtn.classList.remove('bg-blue-500', 'hover:bg-blue-700', 'cursor-pointer');
            }
        }

        document.querySelectorAll('.seat-btn:not([disabled])').forEach(btn => {
            btn.addEventListener('click', function() {
                const seatId = this.dataset.seatId;
                const seatNumber = this.dataset.seatNumber;

                // Check if this seat is already selected by another passenger
                const alreadySelectedBy = Object.entries(selectedSeats).find(([idx, id]) => id === seatId);
                if (alreadySelectedBy && alreadySelectedBy[0] != currentPassengerIndex) {
                    alert('This seat is already selected for another passenger.');
                    return;
                }

                // Remove previous selection for current passenger
                if (selectedSeats[currentPassengerIndex]) {
                    const prevBtn = document.querySelector(`[data-seat-id="${selectedSeats[currentPassengerIndex]}"]`);
                    if (prevBtn) {
                        prevBtn.classList.remove('bg-blue-500', 'border-blue-700', 'text-white');
                        prevBtn.classList.add('bg-green-100', 'border-green-400', 'text-green-700');
                    }
                }

                // Mark current seat as selected
                this.classList.remove('bg-green-100', 'border-green-400', 'text-green-700');
                this.classList.add('bg-blue-500', 'border-blue-700', 'text-white');

                // Store selection
                selectedSeats[currentPassengerIndex] = seatId;
                document.getElementById(`seat-input-${currentPassengerIndex}`).value = seatId;
                document.getElementById(`summary-${currentPassengerIndex}`).textContent = seatNumber;

                // Move to next passenger if available
                if (currentPassengerIndex < passengers.length - 1) {
                    setTimeout(() => {
                        currentPassengerIndex++;
                        updateCurrentPassenger();
                    }, 300);
                }

                updateSubmitButton();
            });
        });

        updateCurrentPassenger();
        updateSubmitButton();
    </script>
    @endpush
</x-app-layout>
