<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Flight;
use App\Services\OverbookingService;

class FlagOverbookedFlightsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'overbooking:flag-flights';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flag flights that are overbooked and approaching departure time';

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
        $this->info('Checking for overbooked flights approaching departure...');

        // Find flights within 2 hours of departure that are overbooked
        $flights = Flight::where('departure_time', '>', now())
            ->where('departure_time', '<=', now()->addHours(2))
            ->whereIn('status', ['scheduled', 'boarding'])
            ->get();

        if ($flights->isEmpty()) {
            $this->info('No flights within boarding window found.');
            return 0;
        }

        $this->info("Found {$flights->count()} flights approaching departure.");
        
        $flaggedCount = 0;
        $overbookedFlights = [];

        foreach ($flights as $flight) {
            $needsFlag = $this->overbookingService->flagOverbookedFlight($flight);
            
            if ($needsFlag) {
                $flaggedCount++;
                $overbookedFlights[] = $flight;
            }
        }

        if ($flaggedCount > 0) {
            $this->warn("⚠️  Found {$flaggedCount} overbooked flights requiring manual resolution!");
            $this->newLine();

            $data = [];
            foreach ($overbookedFlights as $flight) {
                $stats = $this->overbookingService->getOverbookingStats($flight);
                $risk = $this->overbookingService->calculateDeniedBoardingRisk($flight);
                $expectedNoShows = $this->overbookingService->calculateExpectedNoShows($flight);

                $data[] = [
                    $flight->flight_number,
                    $flight->origin . ' → ' . $flight->destination,
                    $flight->departure_time->format('h:i A'),
                    $stats['overbooked_count'],
                    round($expectedNoShows, 1),
                    ucfirst($risk['risk_level']),
                    $risk['risk_score'],
                ];
            }

            $this->table(
                ['Flight', 'Route', 'Departure', 'Overbooked', 'Expected No-Shows', 'Risk', 'Risk Score'],
                $data
            );

            $this->newLine();
            $this->warn('These flights require admin intervention to resolve overbooking.');
            $this->info('Go to /admin/overbooking to manage denied boardings.');
        } else {
            $this->info('✓ No overbooked flights require flagging at this time.');
        }

        return 0;
    }
}
