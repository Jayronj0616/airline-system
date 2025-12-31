<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Mail\CheckInReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendCheckInReminders extends Command
{
    protected $signature = 'bookings:send-checkin-reminders';
    protected $description = 'Send check-in reminder emails to passengers 24 hours before departure';

    public function handle()
    {
        $now = Carbon::now();
        $checkInWindow = Carbon::now()->addHours(24);

        // Get confirmed bookings with flights departing in ~24 hours
        $bookings = Booking::where('status', 'confirmed')
            ->whereHas('flight', function ($query) use ($now, $checkInWindow) {
                $query->whereBetween('departure_time', [$now, $checkInWindow]);
            })
            ->whereDoesntHave('checkIns') // Haven't checked in yet
            ->with(['flight', 'passengers'])
            ->get();

        $sentCount = 0;
        $failedCount = 0;

        foreach ($bookings as $booking) {
            try {
                Mail::to($booking->passengers->first()->email)
                    ->send(new CheckInReminder($booking));
                
                $sentCount++;
                
                Log::channel('bookings')->info('Check-in reminder sent', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'flight_id' => $booking->flight->id,
                ]);
            } catch (\Exception $e) {
                $failedCount++;
                
                Log::channel('failures')->error('Failed to send check-in reminder', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Check-in reminders sent: {$sentCount}");
        if ($failedCount > 0) {
            $this->warn("Failed to send: {$failedCount}");
        }

        return 0;
    }
}
