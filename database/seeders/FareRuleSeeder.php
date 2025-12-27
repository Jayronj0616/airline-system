<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FareClass;
use App\Models\FareRule;

class FareRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fareClasses = FareClass::all();

        foreach ($fareClasses as $fareClass) {
            $rule = match($fareClass->code) {
                'Y' => [ // Economy
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
                ],
                'J' => [ // Business
                    'is_refundable' => true,
                    'refund_fee_percentage' => 10,
                    'change_fee' => 0,
                    'cancellation_fee' => 0,
                    'checked_bags_allowed' => 2,
                    'bag_weight_limit_kg' => 32,
                    'seat_selection_free' => true,
                    'seat_selection_fee' => 0,
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
                ],
                'F' => [ // First Class
                    'is_refundable' => true,
                    'refund_fee_percentage' => 0,
                    'change_fee' => 0,
                    'cancellation_fee' => 0,
                    'checked_bags_allowed' => 3,
                    'bag_weight_limit_kg' => 32,
                    'seat_selection_free' => true,
                    'seat_selection_fee' => 0,
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
                ],
            };

            FareRule::create([
                'fare_class_id' => $fareClass->id,
                ...$rule,
            ]);
        }
    }
}
