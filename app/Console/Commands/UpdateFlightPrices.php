<?php

namespace App\Console\Commands;

use App\Models\Flight;
use App\Services\PricingService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateFlightPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pricing:update 
                            {--flight= : Specific flight ID to update}
                            {--all : Update all future flights}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update flight prices based on current time, inventory, and demand factors';

    protected $pricingService;

    /**
     * Create a new command instance.
     */
    public function __construct(PricingService $pricingService)
    {
        parent::__construct();
        $this->pricingService = $pricingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting price update process...');

        if ($this->option('flight')) {
            // Update specific flight
            $flight = Flight::find($this->option('flight'));
            
            if (!$flight) {
                $this->error("Flight not found with ID: {$this->option('flight')}");
                return 1;
            }

            $this->updateFlight($flight);
            $this->info("✓ Prices updated for flight {$flight->flight_number}");
            
            return 0;
        }

        // Update all future flights (default behavior or with --all flag)
        $flights = Flight::where('departure_time', '>', Carbon::now())
            ->orderBy('departure_time')
            ->get();

        if ($flights->isEmpty()) {
            $this->warn('No future flights found to update.');
            return 0;
        }

        $this->info("Found {$flights->count()} flight(s) to update.");
        
        $bar = $this->output->createProgressBar($flights->count());
        $bar->start();

        $updated = 0;
        foreach ($flights as $flight) {
            $this->updateFlight($flight);
            $updated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ Successfully updated prices for {$updated} flight(s).");

        return 0;
    }

    /**
     * Update prices for a single flight.
     */
    protected function updateFlight(Flight $flight)
    {
        $prices = $this->pricingService->updateFlightPrices($flight);
        
        // Log details if verbose
        if ($this->output->isVerbose()) {
            $this->line("  Flight: {$flight->flight_number}");
            foreach ($prices as $fareClassId => $price) {
                $this->line("    Fare Class {$fareClassId}: ₱" . number_format($price, 2));
            }
        }
    }
}
