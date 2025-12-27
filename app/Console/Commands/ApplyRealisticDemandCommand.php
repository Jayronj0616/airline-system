<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Flight;
use App\Services\DemandPatternService;

class ApplyRealisticDemandCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demand:apply-patterns 
                            {--future-only : Only apply to future flights}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply realistic demand patterns to flights based on route popularity, weekends, holidays, and time of day';

    protected $demandService;

    public function __construct(DemandPatternService $demandService)
    {
        parent::__construct();
        $this->demandService = $demandService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Applying realistic demand patterns to flights...');

        // Get flights based on option
        $query = Flight::query();
        
        if ($this->option('future-only')) {
            $query->where('departure_time', '>', now());
            $this->info('Processing future flights only...');
        } else {
            $this->info('Processing all flights...');
        }

        $flights = $query->get();
        $count = $flights->count();

        if ($count === 0) {
            $this->warn('No flights found to process.');
            return 0;
        }

        $this->info("Found {$count} flights to process.");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($flights as $flight) {
            // Apply realistic demand
            $this->demandService->applyRealisticDemand($flight);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ Successfully applied realistic demand patterns to {$count} flights.");

        // Show some examples
        $this->newLine();
        $this->info('Sample demand scores:');
        $samples = $flights->random(min(5, $count));
        
        $this->table(
            ['Flight', 'Route', 'Departure', 'Day', 'Time', 'Demand Score'],
            $samples->map(function ($flight) {
                return [
                    $flight->flight_number,
                    $flight->origin . ' → ' . $flight->destination,
                    $flight->departure_time->format('M d, Y'),
                    $flight->departure_time->format('l'),
                    $flight->departure_time->format('h:i A'),
                    round($flight->demand_score, 2),
                ];
            })
        );

        return 0;
    }
}
