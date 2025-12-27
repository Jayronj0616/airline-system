<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FareClass;

class FareClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fareClasses = [
            [
                'name' => 'Economy',
                'code' => 'Y',
                'description' => 'Standard economy seating with complimentary snacks and beverages.',
                'max_overbooking_percentage' => 15.00, // Economy: 15% max (higher no-show rate)
                'no_show_probability' => 12.50, // 10-15% no-show rate, average 12.5%
            ],
            [
                'name' => 'Business',
                'code' => 'J',
                'description' => 'Premium business class with extra legroom, priority boarding, and enhanced meals.',
                'max_overbooking_percentage' => 8.00, // Business: 8% max (moderate no-show rate)
                'no_show_probability' => 6.50, // 5-8% no-show rate, average 6.5%
            ],
            [
                'name' => 'First Class',
                'code' => 'F',
                'description' => 'Luxury first class with lie-flat seats, gourmet dining, and exclusive lounge access.',
                'max_overbooking_percentage' => 5.00, // First: 5% max (low no-show rate)
                'no_show_probability' => 3.50, // 2-5% no-show rate, average 3.5%
            ],
        ];

        foreach ($fareClasses as $data) {
            FareClass::create($data);
        }
    }
}
