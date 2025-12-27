<?php

namespace App\Console\Commands;

use App\Models\Flight;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DemandSimulate extends Command
{
    protected $signature = 'demand:simulate 
                            {--flights-percent=30 : Percentage of flights to simulate (20-50)}
                            {--searches-min=5 : Minimum searches per flight}
                            {--searches-max=20 : Maximum searches per flight}
                            {--bookings-min=1 : Minimum bookings per flight}
                            {--bookings-max=5 : Maximum bookings per flight}
                            {--dry-run : Preview changes without saving}
                            {--verbose : Show detailed output}';

    protected $description = 'Simulate user activity (searches and bookings) to create realistic demand patterns';

    public function handle()
    {
        try {
            Log::channel('bookings')->info('Demand simulation started');

            $isDryRun = $this->option('dry-run');
            $isVerbose = $this->option('verbose');
            
            // Get configuration
            $flightsPercent = max(20, min(50, (int)$this->option('flights-percent')));
            $searchesMin = (int)$this->option('searches-min');
            $searchesMax = (int)$this->option('searches-max');
            $bookingsMin = (int)$this->option('bookings-min');
            $bookingsMax = (int)$this->option('bookings-max');

            $this->info('Starting demand simulation...');
            
            if ($isDryRun) {
                $this->warn('DRY RUN MODE - No changes will be saved');
            }
            
            $this->line("Configuration:");
            $this->line("  - Flights to simulate: {$flightsPercent}% of total");
            $this->line("  - Searches per flight: {$searchesMin}-{$searchesMax}");
            $this->line("  - Bookings per flight: {$bookingsMin}-{$bookingsMax}");
            $this->newLine();

            // Get all future flights
            $allFlights = Flight::where('departure_time', '>', now())->get();
            
            if ($allFlights->isEmpty()) {
                $this->info('No future flights found.');
                Log::channel('bookings')->info('Demand simulation completed: No future flights');
                return 0;
            }

            // Select random flights based on percentage
            $flightCount = (int)ceil($allFlights->count() * ($flightsPercent / 100));
            $selectedFlights = $allFlights->random($flightCount);

            $this->info("Selected {$flightCount} flights out of {$allFlights->count()} total flights.");
            
            $stats = [
                'flights_processed' => 0,
                'flights_failed' => 0,
                'total_searches' => 0,
                'total_bookings' => 0,
                'total_demand_increase' => 0,
            ];

            $progressBar = $this->output->createProgressBar($selectedFlights->count());
            
            if (!$isVerbose) {
                $progressBar->start();
            }

            foreach ($selectedFlights as $flight) {
                try {
                    $oldScore = $flight->demand_score;
                    $flightDemandIncrease = 0;
                    
                    // Simulate searches
                    $searchCount = rand($searchesMin, $searchesMax);
                    $stats['total_searches'] += $searchCount;
                    
                    for ($i = 0; $i < $searchCount; $i++) {
                        $searchIncrease = rand(50, 200) / 100; // 0.5 to 2.0
                        $flightDemandIncrease += $searchIncrease;
                        
                        if (!$isDryRun) {
                            $flight->increaseSearchDemand($searchIncrease);
                            $flight->refresh(); // Reload to get updated score
                        }
                    }
                    
                    // Simulate bookings (fewer than searches)
                    $bookingCount = rand($bookingsMin, $bookingsMax);
                    $stats['total_bookings'] += $bookingCount;
                    
                    for ($i = 0; $i < $bookingCount; $i++) {
                        $bookingIncrease = rand(300, 500) / 100; // 3.0 to 5.0
                        $flightDemandIncrease += $bookingIncrease;
                        
                        if (!$isDryRun) {
                            $flight->increaseBookingDemand($bookingIncrease);
                            $flight->refresh(); // Reload to get updated score
                        }
                    }
                    
                    $stats['total_demand_increase'] += $flightDemandIncrease;
                    $stats['flights_processed']++;
                    
                    if ($isVerbose) {
                        $newScore = $isDryRun ? min(100, $oldScore + $flightDemandIncrease) : $flight->demand_score;
                        $this->line(
                            "Flight {$flight->flight_number}: " .
                            "{$searchCount} searches, {$bookingCount} bookings → " .
                            "{$oldScore} → {$newScore} (+{$flightDemandIncrease})"
                        );
                    } else {
                        $progressBar->advance();
                    }

                } catch (\Exception $e) {
                    $stats['flights_failed']++;
                    
                    Log::channel('failures')->error('Demand simulation failed for flight', [
                        'flight_id' => $flight->id,
                        'flight_number' => $flight->flight_number,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    if ($isVerbose) {
                        $this->error("Flight {$flight->flight_number}: Failed - {$e->getMessage()}");
                    }

                    // Continue with next flight instead of stopping
                    continue;
                }
            }

            if (!$isVerbose) {
                $progressBar->finish();
            }
            
            $this->newLine(2);

            // Display summary
            $this->info('Demand Simulation Complete!');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Flights Processed', $stats['flights_processed']],
                    ['Flights Failed', $stats['flights_failed']],
                    ['Total Simulated Searches', $stats['total_searches']],
                    ['Total Simulated Bookings', $stats['total_bookings']],
                    ['Avg Searches per Flight', $stats['flights_processed'] > 0 ? round($stats['total_searches'] / $stats['flights_processed'], 2) : 0],
                    ['Avg Bookings per Flight', $stats['flights_processed'] > 0 ? round($stats['total_bookings'] / $stats['flights_processed'], 2) : 0],
                    ['Total Demand Increase', round($stats['total_demand_increase'], 2)],
                    ['Avg Demand Increase per Flight', $stats['flights_processed'] > 0 ? round($stats['total_demand_increase'] / $stats['flights_processed'], 2) : 0],
                ]
            );

            if ($stats['flights_failed'] > 0) {
                $this->warn("Warning: {$stats['flights_failed']} flight(s) failed to process. Check logs for details.");
            }

            if ($isDryRun) {
                $this->warn('DRY RUN - No changes were saved to the database.');
            }

            Log::channel('bookings')->info('Demand simulation completed', [
                'flights_processed' => $stats['flights_processed'],
                'flights_failed' => $stats['flights_failed'],
                'total_searches' => $stats['total_searches'],
                'total_bookings' => $stats['total_bookings'],
            ]);

            return 0;

        } catch (\Exception $e) {
            Log::channel('failures')->critical('Demand simulation command crashed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Command failed: ' . $e->getMessage());
            return 1;
        }
    }
}
