<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FareClass;
use App\Models\FareRule;

class FareRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all fare classes
        $economy = FareClass::where('code', 'Y')->first();
        $business = FareClass::where('code', 'J')->first();
        $first = FareClass::where('code', 'F')->first();

        if (!$economy || !$business || !$first) {
            $this->command->error('Fare classes not found. Please seed fare_classes first.');
            return;
        }

        // Economy (Basic) Rules
        FareRule::updateOrCreate(
            ['fare_class_id' => $economy->id],
            [
                'is_refundable' => false,
                'refund_fee_percentage' => 0,
                'change_fee' => 100.00,
                'cancellation_fee' => 50.00,
                'checked_bags_allowed' => 1,
                'bag_weight_limit_kg' => 23,
                'seat_selection_free' => false,
                'seat_selection_fee' => 15.00,
                'priority_boarding' => false,
                'cancellation_policy' => 'Non-refundable. Cancellation fee applies.',
                'rules_json' => [
                    'refund_policy' => [
                        'allowed' => false,
                        'fee_percentage' => 0,
                        'min_fee' => 0
                    ],
                    'change_policy' => [
                        'allowed' => true,
                        'fee' => 100,
                        'free_within_hours' => 0
                    ],
                    'baggage' => [
                        'checked_bags' => 1,
                        'weight_limit_kg' => 23,
                        'carry_on_allowed' => true,
                        'carry_on_weight_kg' => 7
                    ],
                    'seat_selection' => [
                        'free' => false,
                        'fee' => 15
                    ],
                    'cancellation' => [
                        'allowed' => true,
                        'fee' => 50,
                        'free_within_hours' => 0
                    ]
                ]
            ]
        );

        $this->command->info('✓ Economy fare rules created');

        // Business Class Rules
        FareRule::updateOrCreate(
            ['fare_class_id' => $business->id],
            [
                'is_refundable' => true,
                'refund_fee_percentage' => 10.00,
                'change_fee' => 0.00,
                'cancellation_fee' => 0.00,
                'checked_bags_allowed' => 2,
                'bag_weight_limit_kg' => 32,
                'seat_selection_free' => true,
                'seat_selection_fee' => 0.00,
                'priority_boarding' => true,
                'cancellation_policy' => 'Refundable with 10% fee. Free changes anytime.',
                'rules_json' => [
                    'refund_policy' => [
                        'allowed' => true,
                        'fee_percentage' => 10,
                        'min_fee' => 50
                    ],
                    'change_policy' => [
                        'allowed' => true,
                        'fee' => 0,
                        'free_within_hours' => 0
                    ],
                    'baggage' => [
                        'checked_bags' => 2,
                        'weight_limit_kg' => 32,
                        'carry_on_allowed' => true,
                        'carry_on_weight_kg' => 10
                    ],
                    'seat_selection' => [
                        'free' => true,
                        'fee' => 0
                    ],
                    'cancellation' => [
                        'allowed' => true,
                        'fee' => 0,
                        'free_within_hours' => 0
                    ],
                    'perks' => [
                        'priority_boarding' => true,
                        'lounge_access' => false,
                        'extra_legroom' => true
                    ]
                ]
            ]
        );

        $this->command->info('✓ Business fare rules created');

        // First Class Rules
        FareRule::updateOrCreate(
            ['fare_class_id' => $first->id],
            [
                'is_refundable' => true,
                'refund_fee_percentage' => 0.00,
                'change_fee' => 0.00,
                'cancellation_fee' => 0.00,
                'checked_bags_allowed' => 3,
                'bag_weight_limit_kg' => 32,
                'seat_selection_free' => true,
                'seat_selection_fee' => 0.00,
                'priority_boarding' => true,
                'cancellation_policy' => 'Fully refundable. Free changes anytime.',
                'rules_json' => [
                    'refund_policy' => [
                        'allowed' => true,
                        'fee_percentage' => 0,
                        'min_fee' => 0
                    ],
                    'change_policy' => [
                        'allowed' => true,
                        'fee' => 0,
                        'free_within_hours' => 0
                    ],
                    'baggage' => [
                        'checked_bags' => 3,
                        'weight_limit_kg' => 32,
                        'carry_on_allowed' => true,
                        'carry_on_weight_kg' => 15
                    ],
                    'seat_selection' => [
                        'free' => true,
                        'fee' => 0
                    ],
                    'cancellation' => [
                        'allowed' => true,
                        'fee' => 0,
                        'free_within_hours' => 0
                    ],
                    'perks' => [
                        'priority_boarding' => true,
                        'lounge_access' => true,
                        'extra_legroom' => true,
                        'premium_meals' => true,
                        'amenity_kit' => true
                    ]
                ]
            ]
        );

        $this->command->info('✓ First Class fare rules created');

        $this->command->info('');
        $this->command->info('All fare rules have been seeded successfully!');
    }
}
