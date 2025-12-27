<?php

namespace Database\Factories;

use App\Models\Aircraft;
use Illuminate\Database\Eloquent\Factories\Factory;

class AircraftFactory extends Factory
{
    protected $model = Aircraft::class;

    public function definition()
    {
        $models = [
            ['model' => 'Boeing 737', 'manufacturer' => 'Boeing', 'total' => 180, 'economy' => 150, 'business' => 24, 'first' => 6],
            ['model' => 'Airbus A320', 'manufacturer' => 'Airbus', 'total' => 180, 'economy' => 150, 'business' => 24, 'first' => 6],
            ['model' => 'Boeing 777', 'manufacturer' => 'Boeing', 'total' => 350, 'economy' => 300, 'business' => 40, 'first' => 10],
        ];

        $selected = $this->faker->randomElement($models);

        return [
            'model' => $selected['model'],
            'manufacturer' => $selected['manufacturer'],
            'total_seats' => $selected['total'],
            'economy_seats' => $selected['economy'],
            'business_seats' => $selected['business'],
            'first_class_seats' => $selected['first'],
        ];
    }
}
