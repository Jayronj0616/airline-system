<?php

namespace App\Helpers;

class FareRuleValidator
{
    /**
     * Validate fare rule JSON structure.
     * 
     * @param array $rules
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validate(array $rules): array
    {
        $errors = [];

        // Validate refund_policy
        if (!isset($rules['refund_policy'])) {
            $errors[] = 'Missing required section: refund_policy';
        } else {
            $refundErrors = self::validateRefundPolicy($rules['refund_policy']);
            $errors = array_merge($errors, $refundErrors);
        }

        // Validate change_policy
        if (!isset($rules['change_policy'])) {
            $errors[] = 'Missing required section: change_policy';
        } else {
            $changeErrors = self::validateChangePolicy($rules['change_policy']);
            $errors = array_merge($errors, $changeErrors);
        }

        // Validate baggage
        if (!isset($rules['baggage'])) {
            $errors[] = 'Missing required section: baggage';
        } else {
            $baggageErrors = self::validateBaggage($rules['baggage']);
            $errors = array_merge($errors, $baggageErrors);
        }

        // Validate seat_selection
        if (!isset($rules['seat_selection'])) {
            $errors[] = 'Missing required section: seat_selection';
        } else {
            $seatErrors = self::validateSeatSelection($rules['seat_selection']);
            $errors = array_merge($errors, $seatErrors);
        }

        // Validate cancellation
        if (!isset($rules['cancellation'])) {
            $errors[] = 'Missing required section: cancellation';
        } else {
            $cancellationErrors = self::validateCancellation($rules['cancellation']);
            $errors = array_merge($errors, $cancellationErrors);
        }

        // Perks are optional, but validate if present
        if (isset($rules['perks'])) {
            $perksErrors = self::validatePerks($rules['perks']);
            $errors = array_merge($errors, $perksErrors);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate refund policy section.
     */
    protected static function validateRefundPolicy(array $policy): array
    {
        $errors = [];

        if (!isset($policy['allowed']) || !is_bool($policy['allowed'])) {
            $errors[] = 'refund_policy.allowed must be a boolean';
        }

        if (isset($policy['fee_percentage'])) {
            if (!is_numeric($policy['fee_percentage']) || $policy['fee_percentage'] < 0 || $policy['fee_percentage'] > 100) {
                $errors[] = 'refund_policy.fee_percentage must be between 0 and 100';
            }
        }

        if (isset($policy['min_fee']) && !is_numeric($policy['min_fee'])) {
            $errors[] = 'refund_policy.min_fee must be numeric';
        }

        if (isset($policy['max_fee']) && !is_numeric($policy['max_fee'])) {
            $errors[] = 'refund_policy.max_fee must be numeric';
        }

        return $errors;
    }

    /**
     * Validate change policy section.
     */
    protected static function validateChangePolicy(array $policy): array
    {
        $errors = [];

        if (!isset($policy['allowed']) || !is_bool($policy['allowed'])) {
            $errors[] = 'change_policy.allowed must be a boolean';
        }

        if (isset($policy['fee']) && !is_numeric($policy['fee'])) {
            $errors[] = 'change_policy.fee must be numeric';
        }

        if (isset($policy['free_within_hours']) && !is_numeric($policy['free_within_hours'])) {
            $errors[] = 'change_policy.free_within_hours must be numeric';
        }

        if (isset($policy['max_changes']) && (!is_int($policy['max_changes']) || $policy['max_changes'] < 0)) {
            $errors[] = 'change_policy.max_changes must be a positive integer';
        }

        return $errors;
    }

    /**
     * Validate baggage section.
     */
    protected static function validateBaggage(array $baggage): array
    {
        $errors = [];

        if (!isset($baggage['checked_bags']) || !is_int($baggage['checked_bags']) || $baggage['checked_bags'] < 0) {
            $errors[] = 'baggage.checked_bags must be a non-negative integer';
        }

        if (!isset($baggage['weight_limit_kg']) || !is_numeric($baggage['weight_limit_kg']) || $baggage['weight_limit_kg'] < 0) {
            $errors[] = 'baggage.weight_limit_kg must be a non-negative number';
        }

        if (isset($baggage['carry_on_allowed']) && !is_bool($baggage['carry_on_allowed'])) {
            $errors[] = 'baggage.carry_on_allowed must be a boolean';
        }

        if (isset($baggage['carry_on_weight_kg']) && (!is_numeric($baggage['carry_on_weight_kg']) || $baggage['carry_on_weight_kg'] < 0)) {
            $errors[] = 'baggage.carry_on_weight_kg must be a non-negative number';
        }

        return $errors;
    }

    /**
     * Validate seat selection section.
     */
    protected static function validateSeatSelection(array $seatSelection): array
    {
        $errors = [];

        if (!isset($seatSelection['free']) || !is_bool($seatSelection['free'])) {
            $errors[] = 'seat_selection.free must be a boolean';
        }

        if (isset($seatSelection['fee']) && (!is_numeric($seatSelection['fee']) || $seatSelection['fee'] < 0)) {
            $errors[] = 'seat_selection.fee must be a non-negative number';
        }

        if (isset($seatSelection['premium_fee']) && (!is_numeric($seatSelection['premium_fee']) || $seatSelection['premium_fee'] < 0)) {
            $errors[] = 'seat_selection.premium_fee must be a non-negative number';
        }

        return $errors;
    }

    /**
     * Validate cancellation section.
     */
    protected static function validateCancellation(array $cancellation): array
    {
        $errors = [];

        if (!isset($cancellation['allowed']) || !is_bool($cancellation['allowed'])) {
            $errors[] = 'cancellation.allowed must be a boolean';
        }

        if (isset($cancellation['fee']) && (!is_numeric($cancellation['fee']) || $cancellation['fee'] < 0)) {
            $errors[] = 'cancellation.fee must be a non-negative number';
        }

        if (isset($cancellation['free_within_hours']) && !is_numeric($cancellation['free_within_hours'])) {
            $errors[] = 'cancellation.free_within_hours must be numeric';
        }

        return $errors;
    }

    /**
     * Validate perks section (optional).
     */
    protected static function validatePerks(array $perks): array
    {
        $errors = [];

        $booleanFields = [
            'priority_boarding',
            'lounge_access',
            'extra_legroom',
            'premium_meals',
            'amenity_kit',
            'fast_track_security'
        ];

        foreach ($booleanFields as $field) {
            if (isset($perks[$field]) && !is_bool($perks[$field])) {
                $errors[] = "perks.{$field} must be a boolean";
            }
        }

        return $errors;
    }

    /**
     * Get default rule structure.
     * 
     * @return array
     */
    public static function getDefaultRules(): array
    {
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
                'fee' => 0,
                'free_within_hours' => 0,
                'max_changes' => 1
            ],
            'baggage' => [
                'checked_bags' => 0,
                'weight_limit_kg' => 0,
                'carry_on_allowed' => true,
                'carry_on_weight_kg' => 7,
                'extra_bag_fee' => 50
            ],
            'seat_selection' => [
                'free' => false,
                'fee' => 0,
                'premium_fee' => 0
            ],
            'cancellation' => [
                'allowed' => true,
                'fee' => 0,
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
}
