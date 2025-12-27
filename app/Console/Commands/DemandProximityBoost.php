<?php

namespace App\Console\Commands;

use App\Models\Flight;
use Illuminate\Console\Command;

class DemandProximityBoost extends Command
{
    protected $signature = 'demand:proximity-boost 
                            {--dry-run : Preview changes without saving}
                            {--verbose : Show detailed output}';

    protected $description = 'Apply departure proximity boosts to flights within 7 days';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isVerbose = $this->option('verbose');

        $this->info('Applying departure proximity boosts...');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be saved');
        }

        // Get flights departing within 7 days
        $flights = Flight::where('departure_time', '>', now())
            ->where('departure_time', '<=', now()->addDays(7))
            ->get();

        if ($flights->isEmpty()) {
            $this->info('No flights found within 7 days of departure.');
            return 0;
        }

        $this->info("Found {$flights->count()} flights within 7 days of departure.");
        
        $stats = [
            'processed' => 0,
            'boosted' => 0,
            'total_boost' => 0,
        ];

        foreach ($flights as $flight) {
            $stats['processed']++;
            
            $oldScore = $flight->demand_score;
            $daysUntil = $flight->days_until_departure;
            
            // Calculate boost
            $boost = 0;
            if ($daysUntil <= 1) {
                $boost = 10;
            } elseif ($daysUntil <= 3) {
                $boost = 7;
            } elseif ($daysUntil <= 7) {
                $boost = 5;
            }
            
            if ($boost > 0 && $oldScore < 100) {
                $newScore = min(100, $oldScore + $boost);
                
                if ($isVerbose) {
                    $this->line("Flight {$flight->flight_number} (T-{$daysUntil}d): {$oldScore} → {$newScore} (+{$boost})");
                }
                
                if (!$isDryRun) {
                    $flight->applyDepartureProximityBoost();
                }
                
                $stats['boosted']++;
                $stats['total_boost'] += $boost;
            }
        }

        $this->newLine();
        $this->info('Proximity Boost Complete!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Flights Processed', $stats['processed']],
                ['Flights Boosted', $stats['boosted']],
                ['Total Boost Points', round($stats['total_boost'], 2)],
            ]
        );

        if ($isDryRun) {
            $this->warn('DRY RUN - No changes were saved to the database.');
        }

        return 0;
    }
}
