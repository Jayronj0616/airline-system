<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Flight;
use App\Models\Aircraft;
use App\Models\Seat;
use App\Models\FareClass;
use App\Services\DemandPatternService;
use Carbon\Carbon;

class FlightSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
        
        // Philippine domestic and international routes
        $routes = [
            // Philippines to Asia
            ['MNL', 'HKG', 2, 5000, 12000, 25000],  // Manila to Hong Kong
            ['HKG', 'MNL', 2, 5000, 12000, 25000],
            ['MNL', 'SIN', 3.5, 6000, 15000, 30000], // Manila to Singapore
            ['SIN', 'MNL', 3.5, 6000, 15000, 30000],
            ['MNL', 'BKK', 3, 5500, 13000, 28000],  // Manila to Bangkok
            ['BKK', 'MNL', 3, 5500, 13000, 28000],
            ['MNL', 'KUL', 3, 5200, 12500, 26000],  // Manila to Kuala Lumpur
            ['KUL', 'MNL', 3, 5200, 12500, 26000],
            ['MNL', 'TPE', 2, 4500, 11000, 23000],  // Manila to Taipei
            ['TPE', 'MNL', 2, 4500, 11000, 23000],
            ['MNL', 'ICN', 4, 7000, 18000, 38000],  // Manila to Seoul
            ['ICN', 'MNL', 4, 7000, 18000, 38000],
            ['MNL', 'NRT', 4.5, 8000, 20000, 42000], // Manila to Tokyo
            ['NRT', 'MNL', 4.5, 8000, 20000, 42000],
            ['MNL', 'PVG', 3, 6500, 16000, 33000],  // Manila to Shanghai
            ['PVG', 'MNL', 3, 6500, 16000, 33000],
            ['MNL', 'HAN', 2.5, 4800, 11500, 24000], // Manila to Hanoi
            ['HAN', 'MNL', 2.5, 4800, 11500, 24000],
            ['MNL', 'SGN', 2.5, 5000, 12000, 25000], // Manila to Ho Chi Minh
            ['SGN', 'MNL', 2.5, 5000, 12000, 25000],
            ['MNL', 'CGK', 3.5, 5800, 14000, 29000],  // Manila to Jakarta
            ['CGK', 'MNL', 3.5, 5800, 14000, 29000],
            ['MNL', 'DPS', 3.5, 6200, 15000, 31000],  // Manila to Bali
            ['DPS', 'MNL', 3.5, 6200, 15000, 31000],
            
            // Philippines to Middle East
            ['MNL', 'DXB', 9, 15000, 40000, 85000],  // Manila to Dubai
            ['DXB', 'MNL', 9, 15000, 40000, 85000],
            ['MNL', 'DOH', 9.5, 16000, 42000, 88000], // Manila to Doha
            ['DOH', 'MNL', 9.5, 16000, 42000, 88000],
            ['MNL', 'AUH', 9, 15500, 41000, 86000],  // Manila to Abu Dhabi
            ['AUH', 'MNL', 9, 15500, 41000, 86000],
            ['MNL', 'RUH', 10, 17000, 43000, 90000],  // Manila to Riyadh
            ['RUH', 'MNL', 10, 17000, 43000, 90000],
            
            // Philippines to Oceania
            ['MNL', 'SYD', 8, 18000, 45000, 95000],  // Manila to Sydney
            ['SYD', 'MNL', 8, 18000, 45000, 95000],
            ['MNL', 'MEL', 8.5, 19000, 47000, 98000], // Manila to Melbourne
            ['MEL', 'MNL', 8.5, 19000, 47000, 98000],
            ['MNL', 'BNE', 8, 17500, 44000, 93000],  // Manila to Brisbane
            ['BNE', 'MNL', 8, 17500, 44000, 93000],
            ['MNL', 'AKL', 10, 20000, 50000, 105000], // Manila to Auckland
            ['AKL', 'MNL', 10, 20000, 50000, 105000],
            
            // Philippines to USA
            ['MNL', 'LAX', 13, 35000, 90000, 180000], // Manila to Los Angeles
            ['LAX', 'MNL', 13, 35000, 90000, 180000],
            ['MNL', 'SFO', 13.5, 36000, 92000, 185000], // Manila to San Francisco
            ['SFO', 'MNL', 13.5, 36000, 92000, 185000],
            ['MNL', 'SEA', 12.5, 34000, 88000, 175000], // Manila to Seattle
            ['SEA', 'MNL', 12.5, 34000, 88000, 175000],
            ['MNL', 'HNL', 10, 28000, 70000, 140000], // Manila to Honolulu
            ['HNL', 'MNL', 10, 28000, 70000, 140000],
            
            // Philippines to Europe
            ['MNL', 'LHR', 14, 40000, 100000, 200000], // Manila to London
            ['LHR', 'MNL', 14, 40000, 100000, 200000],
            ['MNL', 'CDG', 14.5, 41000, 102000, 205000], // Manila to Paris
            ['CDG', 'MNL', 14.5, 41000, 102000, 205000],
            ['MNL', 'FRA', 14, 40000, 100000, 200000], // Manila to Frankfurt
            ['FRA', 'MNL', 14, 40000, 100000, 200000],
            ['MNL', 'AMS', 14, 40000, 100000, 200000], // Manila to Amsterdam
            ['AMS', 'MNL', 14, 40000, 100000, 200000],
            
            // Philippines domestic routes
            ['MNL', 'CEB', 1.5, 2500, 6000, 12000],  // Manila to Cebu
            ['CEB', 'MNL', 1.5, 2500, 6000, 12000],
            ['MNL', 'DVO', 2, 3000, 7000, 14000],    // Manila to Davao
            ['DVO', 'MNL', 2, 3000, 7000, 14000],
            ['MNL', 'ILO', 1.5, 2400, 5800, 11500],  // Manila to Iloilo
            ['ILO', 'MNL', 1.5, 2400, 5800, 11500],
            ['MNL', 'BCD', 1.5, 2300, 5500, 11000],  // Manila to Bacolod
            ['BCD', 'MNL', 1.5, 2300, 5500, 11000],
            ['MNL', 'CRK', 1, 2000, 5000, 10000],    // Manila to Clark
            ['CRK', 'MNL', 1, 2000, 5000, 10000],
            ['CEB', 'DVO', 1.5, 2200, 5300, 10500],  // Cebu to Davao
            ['DVO', 'CEB', 1.5, 2200, 5300, 10500],
            
            // Asia to Asia routes
            ['HKG', 'SIN', 3, 5500, 13000, 27000],   // Hong Kong to Singapore
            ['SIN', 'HKG', 3, 5500, 13000, 27000],
            ['BKK', 'SIN', 2, 4000, 10000, 20000],   // Bangkok to Singapore
            ['SIN', 'BKK', 2, 4000, 10000, 20000],
            ['ICN', 'NRT', 2, 6000, 15000, 30000],   // Seoul to Tokyo
            ['NRT', 'ICN', 2, 6000, 15000, 30000],
            ['HKG', 'NRT', 4, 7000, 17000, 35000],   // Hong Kong to Tokyo
            ['NRT', 'HKG', 4, 7000, 17000, 35000],
            ['SIN', 'DXB', 7, 12000, 30000, 60000],  // Singapore to Dubai
            ['DXB', 'SIN', 7, 12000, 30000, 60000],
            
            // Europe to Europe routes
            ['LHR', 'CDG', 1, 8000, 20000, 40000],   // London to Paris
            ['CDG', 'LHR', 1, 8000, 20000, 40000],
            ['LHR', 'AMS', 1, 8000, 20000, 40000],   // London to Amsterdam
            ['AMS', 'LHR', 1, 8000, 20000, 40000],
            ['CDG', 'FRA', 1, 8000, 20000, 40000],   // Paris to Frankfurt
            ['FRA', 'CDG', 1, 8000, 20000, 40000],
            
            // Transatlantic routes
            ['LHR', 'JFK', 8, 30000, 75000, 150000], // London to New York
            ['JFK', 'LHR', 8, 30000, 75000, 150000],
            ['CDG', 'JFK', 8, 30000, 75000, 150000], // Paris to New York
            ['JFK', 'CDG', 8, 30000, 75000, 150000],
            ['LAX', 'LHR', 11, 35000, 88000, 175000], // Los Angeles to London
            ['LHR', 'LAX', 11, 35000, 88000, 175000],
            
            // Transpacific routes
            ['SYD', 'LAX', 13, 38000, 95000, 190000], // Sydney to Los Angeles
            ['LAX', 'SYD', 13, 38000, 95000, 190000],
            ['SIN', 'SFO', 15, 42000, 105000, 210000], // Singapore to San Francisco
            ['SFO', 'SIN', 15, 42000, 105000, 210000],
            ['HKG', 'LAX', 13, 36000, 90000, 180000], // Hong Kong to Los Angeles
            ['LAX', 'HKG', 13, 36000, 90000, 180000],
        ];

        $flightsCreated = 0;
        $targetFlights = 300; // Increased from 200

        foreach ($routes as $route) {
            if ($flightsCreated >= $targetFlights) break;
            
            [$origin, $destination, $duration, $econPrice, $bizPrice, $firstPrice] = $route;
            
            // Create 2-3 flights per route over next 30 days (increased from 1-2)
            $flightsPerRoute = rand(2, 3);
            
            for ($i = 0; $i < $flightsPerRoute; $i++) {
                if ($flightsCreated >= $targetFlights) break;
                
                // Spread flights more evenly across days, starting from current Manila time
                $dayOffset = ($i * 10) + rand(0, 10); // Space out flights
                $departureTime = Carbon::now('Asia/Manila')->addDays($dayOffset % 30)->setHour(rand(6, 22))->setMinute([0, 15, 30, 45][rand(0, 3)]);
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
        
        $this->command->info("Created {$flightsCreated} flights");
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
