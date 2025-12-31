<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Release expired booking holds every minute
        $schedule->command('bookings:release-expired')->everyMinute();
        
        // Update flight prices every hour
        $schedule->command('pricing:update')->hourly();
        
        // Decay demand scores every hour (Phase 5 Task 2)
        $schedule->command('demand:decay')->hourly();
        
        // Apply departure proximity boosts daily (Phase 5 Task 2)
        $schedule->command('demand:proximity-boost')->daily();
        
        // Simulate demand activity every 15 minutes (Phase 5 Task 3)
        $schedule->command('demand:simulate')->everyFifteenMinutes();
        
        // Enforce overbooking rules every hour (Phase 6 Task 4)
        $schedule->command('overbooking:enforce-rules')->hourly();
        
        // Send check-in reminders (every 6 hours)
        $schedule->command('bookings:send-checkin-reminders')->everySixHours();
        
        // Generate daily flights (runs daily at 2 AM to maintain 30-day window)
        $schedule->command('flights:generate-daily')->dailyAt('02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
