<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CheckIn;
use App\Models\BoardingPass;
use App\Models\Passenger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckInService
{
    /**
     * Check if booking is eligible for check-in.
     * Check-in opens 48 hours before departure.
     */
    public function canCheckIn(Booking $booking): array
    {
        // Must be confirmed
        if (!$booking->isConfirmed()) {
            return [
                'allowed' => false,
                'reason' => 'Booking must be confirmed to check-in.',
            ];
        }

        // Flight must not have departed
        if ($booking->flight->isPast()) {
            return [
                'allowed' => false,
                'reason' => 'Flight has already departed.',
            ];
        }

        // Check-in window (48 hours before departure)
        $checkInOpens = $booking->flight->departure_time->copy()->subHours(48);
        if (Carbon::now()->lessThan($checkInOpens)) {
            return [
                'allowed' => false,
                'reason' => 'Check-in opens 48 hours before departure.',
                'opens_at' => $checkInOpens,
            ];
        }

        // Check-in closes 2 hours before departure
        $checkInCloses = $booking->flight->departure_time->copy()->subHours(2);
        if (Carbon::now()->greaterThan($checkInCloses)) {
            return [
                'allowed' => false,
                'reason' => 'Check-in has closed. Please proceed to airport counter.',
            ];
        }

        // Already checked in
        if ($booking->isCheckedIn()) {
            return [
                'allowed' => false,
                'reason' => 'You have already checked in.',
            ];
        }

        return [
            'allowed' => true,
        ];
    }

    /**
     * Perform check-in for all passengers in booking.
     */
    public function checkIn(Booking $booking): bool
    {
        $eligibility = $this->canCheckIn($booking);
        
        if (!$eligibility['allowed']) {
            throw new \Exception($eligibility['reason']);
        }

        return DB::transaction(function () use ($booking) {
            foreach ($booking->passengers as $passenger) {
                // Create check-in record
                CheckIn::create([
                    'booking_id' => $booking->id,
                    'passenger_id' => $passenger->id,
                    'checked_in_at' => Carbon::now(),
                    'check_in_method' => 'online',
                ]);

                // Generate boarding pass
                $this->generateBoardingPass($booking, $passenger);
            }

            return true;
        });
    }

    /**
     * Generate boarding pass for a passenger.
     */
    protected function generateBoardingPass(Booking $booking, Passenger $passenger): BoardingPass
    {
        // Assign gate (mock - in production this comes from airport systems)
        $gates = ['A1', 'A2', 'A3', 'B1', 'B2', 'B3', 'C1', 'C2', 'C3'];
        $gate = $gates[array_rand($gates)];

        // Boarding time (usually 30-45 minutes before departure)
        $boardingTime = $booking->flight->departure_time->copy()->subMinutes(40);

        // Boarding group (based on fare class)
        $boardingGroup = match($booking->fareClass->code) {
            'F' => '1',
            'J' => '2',
            'Y' => '3',
            default => '3',
        };

        return BoardingPass::create([
            'booking_id' => $booking->id,
            'passenger_id' => $passenger->id,
            'gate' => $gate,
            'boarding_time' => $boardingTime,
            'boarding_group' => $boardingGroup,
        ]);
    }

    /**
     * Get check-in status for booking.
     */
    public function getCheckInStatus(Booking $booking): array
    {
        $totalPassengers = $booking->passengers()->count();
        $checkedInPassengers = $booking->checkIns()->count();

        return [
            'total' => $totalPassengers,
            'checked_in' => $checkedInPassengers,
            'pending' => $totalPassengers - $checkedInPassengers,
            'is_complete' => $checkedInPassengers === $totalPassengers,
        ];
    }
}
