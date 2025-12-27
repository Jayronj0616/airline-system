<?php

namespace App\Console\Commands;

use App\Services\InventoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredHolds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:release-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release expired booking holds (runs every minute via scheduler)';

    /**
     * Execute the console command.
     */
    public function handle(InventoryService $inventoryService)
    {
        try {
            $this->info('Releasing expired holds...');

            $stats = $inventoryService->releaseExpiredHolds();

            if ($stats['found'] === 0) {
                $this->info('No expired holds found.');
                return 0;
            }

            $this->info("Found: {$stats['found']}");
            $this->info("Released: {$stats['released']}");
            $this->info("Seats freed: {$stats['seats_freed']}");

            if ($stats['errors'] > 0) {
                $this->warn("Errors: {$stats['errors']} (check logs)");
            }

            Log::info('Released expired holds', $stats);

            return 0;

        } catch (\Exception $e) {
            Log::error('Release expired holds command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Command failed: ' . $e->getMessage());
            return 1;
        }
    }
}
