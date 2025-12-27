<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Aircraft;

class AircraftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aircraft = [
            [
                'model' => 'Airbus A320',
                'code' => 'A320',
                'total_seats' => 180,
                'economy_seats' => 126,
                'business_seats' => 36,
                'first_class_seats' => 18,
            ],
            [
                'model' => 'Boeing 737-800',
                'code' => 'B738',
                'total_seats' => 189,
                'economy_seats' => 132,
                'business_seats' => 39,
                'first_class_seats' => 18,
            ],
            [
                'model' => 'Boeing 777-300ER',
                'code' => 'B77W',
                'total_seats' => 396,
                'economy_seats' => 277,
                'business_seats' => 79,
                'first_class_seats' => 40,
            ],
            [
                'model' => 'Airbus A350-900',
                'code' => 'A359',
                'total_seats' => 325,
                'economy_seats' => 228,
                'business_seats' => 65,
                'first_class_seats' => 32,
            ],
        ];

        foreach ($aircraft as $data) {
            Aircraft::create($data);
        }
    }
}
