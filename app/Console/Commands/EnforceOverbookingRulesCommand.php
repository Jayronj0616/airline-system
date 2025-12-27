<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Flight;
use App\Services\OverbookingService;

class EnforceOverbookingRulesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'overbooking:enforce-rules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enforce overbooking rules: disable overbooking for flights within 48 hours of departure';

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
        $this->info('Enforcing overbooking rules...');

        // Find flights with overbooking enabled that should be disabled
        $hoursThreshold = OverbookingService::DISABLE_HOURS_BEFORE_DEPARTURE;
        $cutoffTime = now()->addHours($hoursThreshold);

        $flights = Flight::where('overbooking_enabled', true)
            ->where('departure_time', '<=', $cutoffTime)
            ->where('departure_time', '>', now())
            ->whereNotIn('status', ['departed', 'arrived', 'cancelled'])
            ->get();

        if ($flights->isEmpty()) {
            $this->info('No flights need overbooking rules enforced.');
            return 0;
        }

        $this->info("Found {$flights->count()} flights within {$hoursThreshold} hours that need overbooking disabled.");
        $bar = $this->output->createProgressBar($flights->count());
        $bar->start();

        $disabled = 0;

        foreach ($flights as $flight) {
            // Check if overbooking should be disabled
            if (!$this->overbookingService->canOverbook($flight)) {
                $this->overbookingService->disableOverbooking($flight);
                $disabled++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        if ($disabled > 0) {
            $this->info("✓ Disabled overbooking for {$disabled} flights within {$hoursThreshold} hours of departure.");
        } else {
            $this->info('No flights required overbooking to be disabled.');
        }

        // Show summary
        $this->newLine();
        $this->info('Overbooking Rules Summary:');
        $this->table(
            ['Rule', 'Value'],
            [
                ['Min days for overbooking', OverbookingService::MIN_DAYS_FOR_OVERBOOKING . ' days'],
                ['Disable before departure', $hoursThreshold . ' hours'],
                ['Max overbooking percentage', OverbookingService::MAX_OVERBOOKING_PERCENTAGE . '%'],
            ]
        );

        return 0;
    }
}
