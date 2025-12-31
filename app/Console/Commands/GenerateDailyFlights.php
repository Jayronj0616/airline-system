<?php

namespace App\Console\Commands;

use App\Models\Flight;
use App\Models\Aircraft;
use App\Models\Seat;
use App\Models\FareClass;
use App\Services\DemandPatternService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailyFlights extends Command
{
    protected $signature = 'flights:generate-daily';
    protected $description = 'Generate 2-3 flights for each day that needs them (maintains 30-day window)';

    public function handle()
    {
        $aircraft = Aircraft::all();
        $fareClasses = FareClass::all();
        $demandService = app(DemandPatternService::class);
        
        if ($aircraft->isEmpty() || $fareClasses->isEmpty()) {
            $this->error('No aircraft or fare classes found. Run seeders first.');
            return 1;
        }
        
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
            ['MNL', 'BKK', 3, 5500, 13000, 28000],
            ['BKK', 'MNL', 3, 5500, 13000, 28000],
        ];

        $flightsCreated = 0;
        
        // Check each day from tomorrow to 30 days out
        for ($dayOffset = 1; $dayOffset <= 30; $dayOffset++) {
            $date = Carbon::now()->addDays($dayOffset)->startOfDay();
            
            // Check if we already have flights for this date
            $existingFlightsCount = Flight::whereDate('departure_time', $date)->count();
            
            // If we have less than 2 flights, create more
            $flightsNeeded = max(0, 2 - $existingFlightsCount);
            
            if ($flightsNeeded > 0) {
                for ($i = 0; $i < $flightsNeeded; $i++) {
                    // Pick a random route
                    $route = $popularRoutes[array_rand($popularRoutes)];
                    [$origin, $destination, $duration, $econPrice, $bizPrice, $firstPrice] = $route;
                    
                    // Set departure time - spread throughout the day
                    $hour = match($i % 3) {
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
        }
        
        // Clean up old flights (older than yesterday)
        $deletedCount = Flight::where('departure_time', '<', Carbon::yesterday())->delete();
        
        $this->info("✓ Created {$flightsCreated} new flights");
        $this->info("✓ Cleaned up {$deletedCount} old flights");
        $this->info("✓ Maintaining 30-day flight window");
        
        return 0;
    }

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
