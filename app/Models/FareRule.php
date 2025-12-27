<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FareRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'fare_class_id',
        'is_refundable',
        'refund_fee_percentage',
        'change_fee',
        'cancellation_fee',
        'checked_bags_allowed',
        'bag_weight_limit_kg',
        'seat_selection_free',
        'seat_selection_fee',
        'priority_boarding',
        'cancellation_policy',
        'rules_json',
    ];

    protected $casts = [
        'is_refundable' => 'boolean',
        'refund_fee_percentage' => 'decimal:2',
        'change_fee' => 'decimal:2',
        'cancellation_fee' => 'decimal:2',
        'checked_bags_allowed' => 'integer',
        'bag_weight_limit_kg' => 'integer',
        'seat_selection_free' => 'boolean',
        'seat_selection_fee' => 'decimal:2',
        'priority_boarding' => 'boolean',
        'rules_json' => 'array',
    ];

    /**
     * Get the fare class that owns this rule.
     */
    public function fareClass()
    {
        return $this->belongsTo(FareClass::class);
    }

    /**
     * Get custom rules as array.
     * 
     * @return array
     */
    public function getCustomRules(): array
    {
        return $this->rules_json ?? [];
    }

    /**
     * Set custom rules from array.
     * 
     * @param array $rules
     * @return void
     */
    public function setCustomRules(array $rules): void
    {
        $this->rules_json = $rules;
        $this->save();
    }

    /**
     * Get a specific custom rule value.
     * 
     * @param string $key Dot notation key (e.g., 'refund_policy.fee_percentage')
     * @param mixed $default
     * @return mixed
     */
    public function getCustomRule(string $key, $default = null)
    {
        return data_get($this->rules_json, $key, $default);
    }

    /**
     * Get human-readable rule summary.
     * 
     * @return string
     */
    public function getSummary(): string
    {
        $summary = [];

        // Refund policy
        if ($this->is_refundable) {
            if ($this->refund_fee_percentage > 0) {
                $summary[] = "Refundable ({$this->refund_fee_percentage}% fee)";
            } else {
                $summary[] = "Fully refundable";
            }
        } else {
            $summary[] = "Non-refundable";
        }

        // Change policy
        if ($this->change_fee > 0) {
            $summary[] = "₱{$this->change_fee} change fee";
        } else {
            $summary[] = "Free changes";
        }

        // Baggage
        if ($this->checked_bags_allowed > 0) {
            $summary[] = "{$this->checked_bags_allowed} checked bag(s)";
        } else {
            $summary[] = "No checked bags";
        }

        // Seat selection
        if ($this->seat_selection_free) {
            $summary[] = "Free seat selection";
        } elseif ($this->seat_selection_fee > 0) {
            $summary[] = "₱{$this->seat_selection_fee} seat selection";
        }

        return implode(' • ', $summary);
    }
}
