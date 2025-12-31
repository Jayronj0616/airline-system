<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Flight;
use App\Models\Aircraft;
use App\Models\Seat;
use App\Models\FareClass;
use App\Services\DemandPatternService;
use Carbon\Carbon;

class DailyFlightsSeeder extends Seeder
{
    /**
     * Seed flights for every day for the next 30 days
     * Ensures 2-3 flights per day minimum
     */
    public function run(): void
    {
        $aircraft = Aircraft::all();
        $fareClasses = FareClass::all();
        $demandService = new DemandPatternService();
        
        // Get the highest existing flight number
        $lastFlight = Flight::where('flight_number', 'LIKE', 'PR%')
            ->orderByRaw('CAST(SUBSTRING(flight_number, 3) AS UNSIGNED) DESC')
            ->first();
        
        $flightNumber = 100;
        if ($lastFlight) {
            $lastNumber = (int) substr($lastFlight->flight_number, 2);
            $flightNumber = $lastNumber + 1;
        }
        
        // Popular routes for daily flights
        $popularRoutes = [
            ['MNL', 'HKG', 2, 5000, 12000, 25000],
            ['HKG', 'MNL', 2, 5000, 12000, 25000],
            ['MNL', 'SIN', 3.5, 6000, 15000, 30000],
            ['SIN', 'MNL', 3.5, 6000, 15000, 30000],
            ['MNL', 'DXB', 9, 15000, 40000, 85000],
            ['DXB', 'MNL', 9, 15000, 40000, 85000],
            ['MNL', 'DOH', 9.5, 16000, 42000, 88000],
            ['DOH', 'MNL', 9.5, 16000, 42000, 88000],
            ['MNL', 'CEB', 1.5, 2500, 6000, 12000],
            ['CEB', 'MNL', 1.5, 2500, 6000, 12000],
            ['MNL', 'DVO', 2, 3000, 7000, 14000],
            ['DVO', 'MNL', 2, 3000, 7000, 14000],
        ];

        $flightsCreated = 0;
        
        // For each day in the next 30 days
        for ($day = 1; $day <= 30; $day++) {
            $date = Carbon::now()->addDays($day);
            
            // Create 2-3 flights per day with different times
            $flightsPerDay = rand(2, 3);
            
            for ($flightOfDay = 0; $flightOfDay < $flightsPerDay; $flightOfDay++) {
                // Pick a random route
                $route = $popularRoutes[array_rand($popularRoutes)];
                [$origin, $destination, $duration, $econPrice, $bizPrice, $firstPrice] = $route;
                
                // Set departure time - spread throughout the day
                $hour = match($flightOfDay) {
                    0 => rand(6, 10),   // Morning
                    1 => rand(12, 16),  // Afternoon
                    2 => rand(18, 22),  // Evening
                    default => rand(6, 22)
                };
                
                $departureTime = $date->copy()->setHour($hour)->setMinute([0, 15, 30, 45][rand(0, 3)]);
                $arrivalTime = $departureTime->copy()->addHours(floor($duration))->addMinutes(($duration - floor($duration)) * 60);

                $aircraft_model = $aircraft->random();

                $flight = Flight::create([
                    'flight_number' => 'PR' . $flightNumber++,
                    'aircraft_id' => $aircraft_model->id,
                    'origin' => $origin,
                    'destination' => $destination,
                    'departure_time' => $departureTime,
                    'arrival_time' => $arrivalTime,
                    'status' => 'scheduled',
                    'base_price_economy' => $econPrice,
                    'base_price_business' => $bizPrice,
                    'base_price_first' => $firstPrice,
                    'demand_score' => rand(30, 80),
                ]);

                $demandService->applyRealisticDemand($flight);
                $this->createSeatsForFlight($flight, $aircraft_model, $fareClasses);
                
                $flightsCreated++;
            }
        }
        
        $this->command->info("Created {$flightsCreated} flights across 30 days (2-3 flights per day)");
    }

    /**
     * Create physical seats for a flight.
     */
    private function createSeatsForFlight($flight, $aircraft, $fareClasses)
    {
        $economyClass = $fareClasses->where('code', 'Y')->first();
        $businessClass = $fareClasses->where('code', 'J')->first();
        $firstClass = $fareClasses->where('code', 'F')->first();

        // Create First Class seats
        $seatNumber = 1;
        $firstClassRows = ceil($aircraft->first_class_seats / 6);
        for ($row = 1; $row <= $firstClassRows; $row++) {
            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $column) {
                if ($seatNumber > $aircraft->first_class_seats) break;
                
                Seat::create([
                    'flight_id' => $flight->id,
                    'fare_class_id' => $firstClass->id,
                    'seat_number' => $row . $column,
                    'status' => 'available',
                ]);
                $seatNumber++;
            }
        }

        // Create Business Class seats
        $seatNumber = 1;
        $businessRows = ceil($aircraft->business_seats / 6);
        $startRow = $firstClassRows + 1;
        for ($row = $startRow; $row < $startRow + $businessRows; $row++) {
            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $column) {
                if ($seatNumber > $aircraft->business_seats) break;
                
                Seat::create([
                    'flight_id' => $flight->id,
                    'fare_class_id' => $businessClass->id,
                    'seat_number' => $row . $column,
                    'status' => 'available',
                ]);
                $seatNumber++;
            }
        }

        // Create Economy Class seats
        $seatNumber = 1;
        $economyRows = ceil($aircraft->economy_seats / 6);
        $startRow = $firstClassRows + $businessRows + 1;
        for ($row = $startRow; $row < $startRow + $economyRows; $row++) {
            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $column) {
                if ($seatNumber > $aircraft->economy_seats) break;
                
                Seat::create([
                    'flight_id' => $flight->id,
                    'fare_class_id' => $economyClass->id,
                    'seat_number' => $row . $column,
                    'status' => 'available',
                ]);
                $seatNumber++;
            }
        }
    }
}
