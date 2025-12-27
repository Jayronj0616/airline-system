<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Flight;
use App\Services\OverbookingService;

class OverbookingTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'overbooking:test {flight_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test overbooking calculations for a flight';

    protected $overbookingService;

    public function __construct(OverbookingService $overbookingService)
    {
        parent::__construct();
        $this->overbookingService = $overbookingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $flightId = $this->argument('flight_id');

        if ($flightId) {
            $flight = Flight::find($flightId);
            
            if (!$flight) {
                $this->error("Flight #{$flightId} not found.");
                return 1;
            }

            $this->displayFlightOverbookingInfo($flight);
        } else {
            // Show all future flights with overbooking status
            $flights = Flight::with('aircraft')
                ->where('departure_time', '>', now())
                ->orderBy('departure_time')
                ->take(10)
                ->get();

            if ($flights->isEmpty()) {
                $this->warn('No future flights found.');
                return 0;
            }

            $this->info('Overbooking Status for Upcoming Flights:');
            $this->newLine();

            $data = [];
            foreach ($flights as $flight) {
                $stats = $this->overbookingService->getOverbookingStats($flight);
                
                $data[] = [
                    $flight->id,
                    $flight->flight_number,
                    $flight->origin . ' → ' . $flight->destination,
                    $stats['physical_capacity'],
                    $stats['virtual_capacity'],
                    $stats['confirmed_bookings'],
                    $stats['available_seats'],
                    $stats['overbooked_count'],
                    $stats['load_factor'] . '%',
                    $stats['overbooking_enabled'] ? 'Yes' : 'No',
                    $stats['at_risk'] ? '⚠️ YES' : 'No',
                ];
            }

            $this->table(
                ['ID', 'Flight', 'Route', 'Physical', 'Virtual', 'Confirmed', 'Available', 'Overbooked', 'Load %', 'OB Enabled', 'At Risk'],
                $data
            );
        }

        return 0;
    }

    private function displayFlightOverbookingInfo(Flight $flight)
    {
        $stats = $this->overbookingService->getOverbookingStats($flight);

        $this->info("Flight: {$flight->flight_number}");
        $this->info("Route: {$flight->origin} → {$flight->destination}");
        $this->info("Departure: {$flight->departure_time->format('M d, Y h:i A')}");
        $this->info("Aircraft: {$flight->aircraft->model} ({$flight->aircraft->registration})");
        $this->newLine();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Physical Capacity', $stats['physical_capacity'] . ' seats'],
                ['Virtual Capacity', $stats['virtual_capacity'] . ' seats'],
                ['Confirmed Bookings', $stats['confirmed_bookings']],
                ['Available Seats', $stats['available_seats']],
                ['Overbooked Count', $stats['overbooked_count']],
                ['Load Factor', $stats['load_factor'] . '%'],
                ['Overbooking Enabled', $stats['overbooking_enabled'] ? 'Yes' : 'No'],
                ['Overbooking Percentage', $stats['overbooking_percentage'] . '%'],
                ['Can Overbook Now', $stats['can_overbook'] ? 'Yes' : 'No'],
                ['At Risk of Denied Boarding', $stats['at_risk'] ? '⚠️ YES' : 'No'],
                ['Virtual Capacity Reached', $stats['virtual_capacity_reached'] ? 'Yes' : 'No'],
            ]
        );

        $this->newLine();

        // Show interpretation
        if ($stats['at_risk']) {
            $this->warn("⚠️  WARNING: This flight is overbooked by {$stats['overbooked_count']} seats!");
            $this->warn("Risk of denied boarding if all passengers show up.");
        } elseif ($stats['overbooking_enabled'] && $stats['can_overbook']) {
            $this->info("✓ Overbooking is active. Can accept up to {$stats['virtual_capacity']} bookings.");
        } else {
            $this->info("Overbooking is not active for this flight.");
        }

        // Show example calculation
        $this->newLine();
        $this->info("Calculation:");
        $this->line("Virtual Capacity = Physical Capacity × (1 + Overbooking %)");
        $this->line("Virtual Capacity = {$stats['physical_capacity']} × (1 + {$stats['overbooking_percentage']}%) = {$stats['virtual_capacity']}");
    }
}
