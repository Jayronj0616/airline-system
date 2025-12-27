<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\FareClass;
use App\Models\FareRule;
use Carbon\Carbon;

class FareRuleService
{
    /**
     * Check if booking can be refunded and calculate refund fee.
     * 
     * @param Booking $booking
     * @return array ['allowed' => bool, 'fee' => float, 'refund_amount' => float, 'reason' => string|null]
     */
    public function canRefund(Booking $booking): array
    {
        $fareRule = $booking->fareClass->fareRule;

        if (!$fareRule) {
            return [
                'allowed' => false,
                'fee' => 0,
                'refund_amount' => 0,
                'reason' => 'No fare rules defined for this booking'
            ];
        }

        // Check if refund is allowed
        $refundAllowed = $fareRule->getCustomRule('refund_policy.allowed') ?? $fareRule->is_refundable;

        if (!$refundAllowed) {
            return [
                'allowed' => false,
                'fee' => 0,
                'refund_amount' => 0,
                'reason' => 'This fare class is non-refundable'
            ];
        }

        // Check if booking is already cancelled or expired
        if (in_array($booking->status, ['cancelled', 'expired'])) {
            return [
                'allowed' => false,
                'fee' => 0,
                'refund_amount' => 0,
                'reason' => 'Booking is already ' . $booking->status
            ];
        }

        // Check if flight has already departed
        if ($booking->flight->isPast()) {
            return [
                'allowed' => false,
                'fee' => 0,
                'refund_amount' => 0,
                'reason' => 'Flight has already departed'
            ];
        }

        // Check deadline hours
        $deadlineHours = $fareRule->getCustomRule('refund_policy.deadline_hours', 0);
        $hoursUntilDeparture = Carbon::now()->diffInHours($booking->flight->departure_time, false);

        if ($hoursUntilDeparture < $deadlineHours) {
            return [
                'allowed' => false,
                'fee' => 0,
                'refund_amount' => 0,
                'reason' => "Refund must be requested at least {$deadlineHours} hours before departure"
            ];
        }

        // Calculate refund fee
        $feePercentage = $fareRule->getCustomRule('refund_policy.fee_percentage') ?? $fareRule->refund_fee_percentage ?? 0;
        $minFee = $fareRule->getCustomRule('refund_policy.min_fee', 0);
        $maxFee = $fareRule->getCustomRule('refund_policy.max_fee', 999999);

        $calculatedFee = ($booking->total_price * $feePercentage) / 100;
        $refundFee = max($minFee, min($calculatedFee, $maxFee));

        $refundAmount = $booking->total_price - $refundFee;

        return [
            'allowed' => true,
            'fee' => round($refundFee, 2),
            'refund_amount' => round($refundAmount, 2),
            'reason' => null
        ];
    }

    /**
     * Check if booking can be changed and calculate change fee.
     * 
     * @param Booking $booking
     * @return array ['allowed' => bool, 'fee' => float, 'reason' => string|null]
     */
    public function canChangeBooking(Booking $booking): array
    {
        $fareRule = $booking->fareClass->fareRule;

        if (!$fareRule) {
            return [
                'allowed' => false,
                'fee' => 0,
                'reason' => 'No fare rules defined for this booking'
            ];
        }

        // Check if changes are allowed
        $changeAllowed = $fareRule->getCustomRule('change_policy.allowed', true);

        if (!$changeAllowed) {
            return [
                'allowed' => false,
                'fee' => 0,
                'reason' => 'This fare class does not allow booking changes'
            ];
        }

        // Check if booking is in valid state
        if (!in_array($booking->status, ['held', 'confirmed'])) {
            return [
                'allowed' => false,
                'fee' => 0,
                'reason' => 'Only held or confirmed bookings can be changed'
            ];
        }

        // Check if flight has already departed
        if ($booking->flight->isPast()) {
            return [
                'allowed' => false,
                'fee' => 0,
                'reason' => 'Cannot change booking for departed flight'
            ];
        }

        // Check free change window (hours after booking creation)
        $freeWithinHours = $fareRule->getCustomRule('change_policy.free_within_hours', 0);
        $hoursSinceBooking = Carbon::now()->diffInHours($booking->created_at);

        if ($hoursSinceBooking <= $freeWithinHours) {
            return [
                'allowed' => true,
                'fee' => 0,
                'reason' => "Free changes within {$freeWithinHours} hours of booking"
            ];
        }

        // Calculate change fee
        $changeFee = $fareRule->getCustomRule('change_policy.fee') ?? $fareRule->change_fee ?? 0;

        return [
            'allowed' => true,
            'fee' => round($changeFee, 2),
            'reason' => null
        ];
    }

    /**
     * Check if booking can be cancelled and calculate cancellation penalty.
     * 
     * @param Booking $booking
     * @return array ['allowed' => bool, 'fee' => float, 'reason' => string|null]
     */
    public function canCancelBooking(Booking $booking): array
    {
        $fareRule = $booking->fareClass->fareRule;

        if (!$fareRule) {
            return [
                'allowed' => false,
                'fee' => 0,
                'reason' => 'No fare rules defined for this booking'
            ];
        }

        // Check if cancellation is allowed
        $cancellationAllowed = $fareRule->getCustomRule('cancellation.allowed', true);

        if (!$cancellationAllowed) {
            return [
                'allowed' => false,
                'fee' => 0,
                'reason' => 'This fare class does not allow cancellation'
            ];
        }

        // Check if booking is in valid state
        if (!in_array($booking->status, ['held', 'confirmed'])) {
            return [
                'allowed' => false,
                'fee' => 0,
                'reason' => 'Only held or confirmed bookings can be cancelled'
            ];
        }

        // Check if flight has already departed
        if ($booking->flight->isPast()) {
            return [
                'allowed' => false,
                'fee' => 0,
                'reason' => 'Cannot cancel booking for departed flight'
            ];
        }

        // Check free cancellation window (hours after booking creation)
        $freeWithinHours = $fareRule->getCustomRule('cancellation.free_within_hours', 0);
        $hoursSinceBooking = Carbon::now()->diffInHours($booking->created_at);

        if ($hoursSinceBooking <= $freeWithinHours) {
            return [
                'allowed' => true,
                'fee' => 0,
                'reason' => "Free cancellation within {$freeWithinHours} hours of booking"
            ];
        }

        // Check no-refund window (hours before departure)
        $noRefundHours = $fareRule->getCustomRule('cancellation.no_refund_hours', 0);
        $hoursUntilDeparture = Carbon::now()->diffInHours($booking->flight->departure_time, false);

        if ($hoursUntilDeparture < $noRefundHours) {
            return [
                'allowed' => true,
                'fee' => $booking->total_price, // Full penalty - no refund
                'reason' => "No refund for cancellations within {$noRefundHours} hours of departure"
            ];
        }

        // Calculate cancellation fee
        $cancellationFee = $fareRule->getCustomRule('cancellation.fee') ?? $fareRule->cancellation_fee ?? 0;

        return [
            'allowed' => true,
            'fee' => round($cancellationFee, 2),
            'reason' => null
        ];
    }

    /**
     * Get baggage allowance details for a fare class.
     * 
     * @param FareClass $fareClass
     * @return array ['checked_bags' => int, 'weight_limit_kg' => int, 'carry_on_allowed' => bool, 'carry_on_weight_kg' => int, 'extra_bag_fee' => float]
     */
    public function getBaggageAllowance(FareClass $fareClass): array
    {
        $fareRule = $fareClass->fareRule;

        if (!$fareRule) {
            return [
                'checked_bags' => 0,
                'weight_limit_kg' => 0,
                'carry_on_allowed' => false,
                'carry_on_weight_kg' => 0,
                'extra_bag_fee' => 0
            ];
        }

        return [
            'checked_bags' => $fareRule->getCustomRule('baggage.checked_bags') ?? $fareRule->checked_bags_allowed ?? 0,
            'weight_limit_kg' => $fareRule->getCustomRule('baggage.weight_limit_kg') ?? $fareRule->bag_weight_limit_kg ?? 0,
            'carry_on_allowed' => $fareRule->getCustomRule('baggage.carry_on_allowed', true),
            'carry_on_weight_kg' => $fareRule->getCustomRule('baggage.carry_on_weight_kg', 7),
            'extra_bag_fee' => $fareRule->getCustomRule('baggage.extra_bag_fee', 0)
        ];
    }

    /**
     * Get seat selection fee details for a fare class.
     * 
     * @param FareClass $fareClass
     * @return array ['free' => bool, 'standard_fee' => float, 'premium_fee' => float]
     */
    public function getSeatSelectionFee(FareClass $fareClass): array
    {
        $fareRule = $fareClass->fareRule;

        if (!$fareRule) {
            return [
                'free' => false,
                'standard_fee' => 0,
                'premium_fee' => 0
            ];
        }

        $isFree = $fareRule->getCustomRule('seat_selection.free') ?? $fareRule->seat_selection_free ?? false;

        return [
            'free' => $isFree,
            'standard_fee' => $isFree ? 0 : ($fareRule->getCustomRule('seat_selection.fee') ?? $fareRule->seat_selection_fee ?? 0),
            'premium_fee' => $isFree ? 0 : ($fareRule->getCustomRule('seat_selection.premium_fee', 0))
        ];
    }

    /**
     * Get all perks included with a fare class.
     * 
     * @param FareClass $fareClass
     * @return array
     */
    public function getPerks(FareClass $fareClass): array
    {
        $fareRule = $fareClass->fareRule;

        if (!$fareRule) {
            return [
                'priority_boarding' => false,
                'lounge_access' => false,
                'extra_legroom' => false,
                'premium_meals' => false,
                'amenity_kit' => false,
                'fast_track_security' => false
            ];
        }

        return [
            'priority_boarding' => $fareRule->getCustomRule('perks.priority_boarding') ?? $fareRule->priority_boarding ?? false,
            'lounge_access' => $fareRule->getCustomRule('perks.lounge_access', false),
            'extra_legroom' => $fareRule->getCustomRule('perks.extra_legroom', false),
            'premium_meals' => $fareRule->getCustomRule('perks.premium_meals', false),
            'amenity_kit' => $fareRule->getCustomRule('perks.amenity_kit', false),
            'fast_track_security' => $fareRule->getCustomRule('perks.fast_track_security', false)
        ];
    }

    /**
     * Get complete rule summary for a fare class.
     * Useful for displaying on booking pages.
     * 
     * @param FareClass $fareClass
     * @return array
     */
    public function getRuleSummary(FareClass $fareClass): array
    {
        $fareRule = $fareClass->fareRule;

        if (!$fareRule) {
            return [
                'refundable' => false,
                'change_fee' => 0,
                'baggage' => '0 bags',
                'seat_selection' => 'Paid',
                'perks' => []
            ];
        }

        // Refund summary
        $refundAllowed = $fareRule->getCustomRule('refund_policy.allowed') ?? $fareRule->is_refundable;
        $refundFeePercentage = $fareRule->getCustomRule('refund_policy.fee_percentage') ?? $fareRule->refund_fee_percentage ?? 0;

        if (!$refundAllowed) {
            $refundSummary = 'Non-refundable';
        } elseif ($refundFeePercentage == 0) {
            $refundSummary = 'Fully refundable';
        } else {
            $refundSummary = "Refundable ({$refundFeePercentage}% fee)";
        }

        // Change fee summary
        $changeFee = $fareRule->getCustomRule('change_policy.fee') ?? $fareRule->change_fee ?? 0;
        $changeSummary = $changeFee == 0 ? 'Free changes' : "₱{$changeFee} change fee";

        // Baggage summary
        $baggage = $this->getBaggageAllowance($fareClass);
        $baggageSummary = $baggage['checked_bags'] == 0
            ? 'No checked bags'
            : "{$baggage['checked_bags']} bag(s) ({$baggage['weight_limit_kg']}kg each)";

        // Seat selection summary
        $seatSelection = $this->getSeatSelectionFee($fareClass);
        $seatSummary = $seatSelection['free'] ? 'Free seat selection' : "₱{$seatSelection['standard_fee']} seat selection";

        // Active perks
        $perks = $this->getPerks($fareClass);
        $activePerks = array_keys(array_filter($perks));

        return [
            'refundable' => $refundSummary,
            'change_fee' => $changeSummary,
            'baggage' => $baggageSummary,
            'seat_selection' => $seatSummary,
            'perks' => $activePerks
        ];
    }
}
