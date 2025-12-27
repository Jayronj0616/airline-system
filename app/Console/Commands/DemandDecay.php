<?php

namespace App\Console\Commands;

use App\Models\Flight;
use Illuminate\Console\Command;

class DemandDecay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demand:decay 
                            {--dry-run : Preview changes without saving}
                            {--verbose : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Decay demand scores for flights with no recent activity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isVerbose = $this->option('verbose');

        $this->info('Starting demand decay process...');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be saved');
        }

        // Get all future flights
        $flights = Flight::where('departure_time', '>', now())
            ->where('demand_score', '>', 0) // Only flights with positive demand
            ->get();

        if ($flights->isEmpty()) {
            $this->info('No flights found for demand decay.');
            return 0;
        }

        $this->info("Found {$flights->count()} flights to process.");
        
        if ($this->option('verbose')) {
            $this->newLine();
        }

        $stats = [
            'processed' => 0,
            'decayed' => 0,
            'skipped' => 0,
            'total_decay' => 0,
        ];

        $progressBar = $this->output->createProgressBar($flights->count());
        $progressBar->start();

        foreach ($flights as $flight) {
            $stats['processed']++;
            
            $oldScore = $flight->demand_score;
            $decayAmount = rand(100, 300) / 100; // Random 1.0 to 3.0
            $newScore = max(0, $oldScore - $decayAmount);

            // Only decay if score will actually change
            if ($newScore == $oldScore) {
                $stats['skipped']++;
                $progressBar->advance();
                continue;
            }

            if ($isVerbose) {
                $this->newLine();
                $this->line("Flight {$flight->flight_number}: {$oldScore} → {$newScore} (-{$decayAmount})");
            }

            if (!$isDryRun) {
                $flight->decayDemand($decayAmount);
            }

            $stats['decayed']++;
            $stats['total_decay'] += $decayAmount;
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display summary
        $this->info('Demand Decay Complete!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Flights Processed', $stats['processed']],
                ['Flights Decayed', $stats['decayed']],
                ['Flights Skipped', $stats['skipped']],
                ['Total Decay Points', round($stats['total_decay'], 2)],
                ['Average Decay', $stats['decayed'] > 0 ? round($stats['total_decay'] / $stats['decayed'], 2) : 0],
            ]
        );

        if ($isDryRun) {
            $this->warn('DRY RUN - No changes were saved to the database.');
        }

        return 0;
    }
}
