<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FareClass;
use App\Models\FareRule;
use App\Services\FareRuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FareRulesController extends Controller
{
    protected $fareRuleService;

    public function __construct(FareRuleService $fareRuleService)
    {
        $this->fareRuleService = $fareRuleService;
    }

    /**
     * Display all fare classes with their rules.
     */
    public function index()
    {
        $fareClasses = FareClass::with('fareRule')->get();

        return view('admin.fare-rules.index', compact('fareClasses'));
    }

    /**
     * Show the form for editing fare rules.
     */
    public function edit(FareClass $fareClass)
    {
        $fareClass->load('fareRule');
        
        // Create default rule if doesn't exist
        if (!$fareClass->fareRule) {
            $fareClass->fareRule()->create([
                'is_refundable' => false,
                'refund_fee_percentage' => 0,
                'change_fee' => 0,
                'cancellation_fee' => 0,
                'checked_bags_allowed' => 1,
                'bag_weight_limit_kg' => 23,
                'seat_selection_free' => false,
                'seat_selection_fee' => 15,
                'priority_boarding' => false,
                'rules_json' => $this->getDefaultRulesJson($fareClass->code),
            ]);
            
            $fareClass->load('fareRule');
        }

        // Get rule summary for preview
        $ruleSummary = $this->fareRuleService->getRuleSummary($fareClass);

        return view('admin.fare-rules.edit', compact('fareClass', 'ruleSummary'));
    }

    /**
     * Update fare rules.
     */
    public function update(Request $request, FareClass $fareClass)
    {
        $validated = $request->validate([
            'is_refundable' => 'required|boolean',
            'refund_fee_percentage' => 'required|numeric|min:0|max:100',
            'change_fee' => 'required|numeric|min:0',
            'cancellation_fee' => 'required|numeric|min:0',
            'checked_bags_allowed' => 'required|integer|min:0',
            'bag_weight_limit_kg' => 'required|integer|min:0',
            'seat_selection_free' => 'required|boolean',
            'seat_selection_fee' => 'required|numeric|min:0',
            'priority_boarding' => 'required|boolean',
            'rules_json' => 'nullable|json',
        ]);

        // Validate JSON if provided
        if ($request->filled('rules_json')) {
            $jsonValidation = $this->validateRulesJson($request->rules_json);
            
            if (!$jsonValidation['valid']) {
                return back()->withErrors(['rules_json' => $jsonValidation['error']])->withInput();
            }
            
            $validated['rules_json'] = json_decode($request->rules_json, true);
        }

        $fareClass->fareRule()->updateOrCreate(
            ['fare_class_id' => $fareClass->id],
            $validated
        );

        return redirect()
            ->route('admin.fare-rules.edit', $fareClass)
            ->with('success', 'Fare rules updated successfully.');
    }

    /**
     * Validate rules JSON structure.
     */
    protected function validateRulesJson(string $json): array
    {
        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'valid' => false,
                'error' => 'Invalid JSON format: ' . json_last_error_msg()
            ];
        }

        // Check required fields
        $requiredFields = [
            'refund_policy',
            'change_policy',
            'baggage',
            'seat_selection',
            'cancellation',
        ];

        foreach ($requiredFields as $field) {
            if (!isset($decoded[$field])) {
                return [
                    'valid' => false,
                    'error' => "Missing required field: {$field}"
                ];
            }
        }

        // Validate refund_policy structure
        if (!isset($decoded['refund_policy']['allowed']) || !is_bool($decoded['refund_policy']['allowed'])) {
            return [
                'valid' => false,
                'error' => 'refund_policy.allowed must be a boolean'
            ];
        }

        // Validate change_policy structure
        if (!isset($decoded['change_policy']['allowed']) || !is_bool($decoded['change_policy']['allowed'])) {
            return [
                'valid' => false,
                'error' => 'change_policy.allowed must be a boolean'
            ];
        }

        // Validate baggage structure
        if (!isset($decoded['baggage']['checked_bags']) || !is_int($decoded['baggage']['checked_bags'])) {
            return [
                'valid' => false,
                'error' => 'baggage.checked_bags must be an integer'
            ];
        }

        return ['valid' => true];
    }

    /**
     * Get default rules JSON based on fare class.
     */
    protected function getDefaultRulesJson(string $fareClassCode): array
    {
        $code = strtolower($fareClassCode);

        // Economy defaults
        if (in_array($code, ['y', 'economy'])) {
            return [
                'refund_policy' => [
                    'allowed' => false,
                    'fee_percentage' => 0,
                    'min_fee' => 0,
                    'max_fee' => 0,
                    'deadline_hours' => 0
                ],
                'change_policy' => [
                    'allowed' => true,
                    'fee' => 100,
                    'free_within_hours' => 0,
                    'max_changes' => 1
                ],
                'baggage' => [
                    'checked_bags' => 1,
                    'weight_limit_kg' => 23,
                    'carry_on_allowed' => true,
                    'carry_on_weight_kg' => 7,
                    'extra_bag_fee' => 75
                ],
                'seat_selection' => [
                    'free' => false,
                    'fee' => 15,
                    'premium_fee' => 40
                ],
                'cancellation' => [
                    'allowed' => true,
                    'fee' => 50,
                    'free_within_hours' => 0,
                    'no_refund_hours' => 24
                ],
                'perks' => [
                    'priority_boarding' => false,
                    'lounge_access' => false,
                    'extra_legroom' => false,
                    'premium_meals' => false,
                    'amenity_kit' => false,
                    'fast_track_security' => false
                ]
            ];
        }

        // Business defaults
        if (in_array($code, ['j', 'business'])) {
            return [
                'refund_policy' => [
                    'allowed' => true,
                    'fee_percentage' => 10,
                    'min_fee' => 50,
                    'max_fee' => 500,
                    'deadline_hours' => 24
                ],
                'change_policy' => [
                    'allowed' => true,
                    'fee' => 0,
                    'free_within_hours' => 0,
                    'max_changes' => 999
                ],
                'baggage' => [
                    'checked_bags' => 2,
                    'weight_limit_kg' => 32,
                    'carry_on_allowed' => true,
                    'carry_on_weight_kg' => 10,
                    'extra_bag_fee' => 50
                ],
                'seat_selection' => [
                    'free' => true,
                    'fee' => 0,
                    'premium_fee' => 0
                ],
                'cancellation' => [
                    'allowed' => true,
                    'fee' => 0,
                    'free_within_hours' => 0,
                    'no_refund_hours' => 2
                ],
                'perks' => [
                    'priority_boarding' => true,
                    'lounge_access' => false,
                    'extra_legroom' => true,
                    'premium_meals' => true,
                    'amenity_kit' => false,
                    'fast_track_security' => false
                ]
            ];
        }

        // First Class defaults
        if (in_array($code, ['f', 'first'])) {
            return [
                'refund_policy' => [
                    'allowed' => true,
                    'fee_percentage' => 0,
                    'min_fee' => 0,
                    'max_fee' => 0,
                    'deadline_hours' => 2
                ],
                'change_policy' => [
                    'allowed' => true,
                    'fee' => 0,
                    'free_within_hours' => 0,
                    'max_changes' => 999
                ],
                'baggage' => [
                    'checked_bags' => 3,
                    'weight_limit_kg' => 32,
                    'carry_on_allowed' => true,
                    'carry_on_weight_kg' => 15,
                    'extra_bag_fee' => 25
                ],
                'seat_selection' => [
                    'free' => true,
                    'fee' => 0,
                    'premium_fee' => 0
                ],
                'cancellation' => [
                    'allowed' => true,
                    'fee' => 0,
                    'free_within_hours' => 0,
                    'no_refund_hours' => 1
                ],
                'perks' => [
                    'priority_boarding' => true,
                    'lounge_access' => true,
                    'extra_legroom' => true,
                    'premium_meals' => true,
                    'amenity_kit' => true,
                    'fast_track_security' => true
                ]
            ];
        }

        // Generic default
        return [
            'refund_policy' => ['allowed' => false, 'fee_percentage' => 0],
            'change_policy' => ['allowed' => true, 'fee' => 100],
            'baggage' => ['checked_bags' => 1, 'weight_limit_kg' => 23],
            'seat_selection' => ['free' => false, 'fee' => 15],
            'cancellation' => ['allowed' => true, 'fee' => 50],
            'perks' => [
                'priority_boarding' => false,
                'lounge_access' => false,
                'extra_legroom' => false,
                'premium_meals' => false,
                'amenity_kit' => false,
                'fast_track_security' => false
            ]
        ];
    }
}
