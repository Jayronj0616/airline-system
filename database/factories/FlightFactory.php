<?php

namespace Database\Factories;

use App\Models\Flight;
use App\Models\Aircraft;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class FlightFactory extends Factory
{
    protected $model = Flight::class;

    public function definition()
    {
        $departureTime = Carbon::now()->addDays(rand(1, 60));
        $arrivalTime = (clone $departureTime)->addHours(rand(1, 8));

        return [
            'flight_number' => 'FL' . $this->faker->unique()->numberBetween(1000, 9999),
            'aircraft_id' => Aircraft::factory(),
            'origin' => $this->faker->city(),
            'destination' => $this->faker->city(),
            'departure_time' => $departureTime,
            'arrival_time' => $arrivalTime,
            'status' => 'scheduled',
            'base_price_economy' => rand(100, 500),
            'base_price_business' => rand(500, 1500),
            'base_price_first' => rand(1500, 5000),
            'demand_score' => rand(0, 100),
        ];
    }
}
