# Fare Rules - JSON Schema Documentation

## Overview
This document defines the JSON schema used in the `rules_json` column of the `fare_rules` table. This flexible JSON format allows for custom business rules without requiring database schema changes.

---

## Complete JSON Schema

```json
{
  "refund_policy": {
    "allowed": true,
    "fee_percentage": 10,
    "min_fee": 50,
    "max_fee": 500,
    "deadline_hours": 24
  },
  "change_policy": {
    "allowed": true,
    "fee": 100,
    "free_within_hours": 24,
    "max_changes": 3
  },
  "baggage": {
    "checked_bags": 2,
    "weight_limit_kg": 23,
    "carry_on_allowed": true,
    "carry_on_weight_kg": 7,
    "extra_bag_fee": 50
  },
  "seat_selection": {
    "free": false,
    "fee": 20,
    "premium_fee": 50
  },
  "cancellation": {
    "allowed": true,
    "fee": 50,
    "free_within_hours": 24,
    "no_refund_hours": 2
  },
  "perks": {
    "priority_boarding": true,
    "lounge_access": false,
    "extra_legroom": true,
    "premium_meals": false,
    "amenity_kit": false,
    "fast_track_security": false
  }
}
```

---

## Field Definitions

### 1. Refund Policy
Controls how refunds are processed for this fare class.

```json
"refund_policy": {
  "allowed": true,              // Can this fare be refunded?
  "fee_percentage": 10,          // Refund fee as percentage (0-100)
  "min_fee": 50,                 // Minimum refund fee (₱)
  "max_fee": 500,                // Maximum refund fee (₱)
  "deadline_hours": 24           // Hours before departure to request refund
}
```

**Examples:**

**Non-refundable (Economy Basic):**
```json
"refund_policy": {
  "allowed": false,
  "fee_percentage": 0,
  "min_fee": 0,
  "max_fee": 0,
  "deadline_hours": 0
}
```

**Partially Refundable (Business):**
```json
"refund_policy": {
  "allowed": true,
  "fee_percentage": 10,
  "min_fee": 50,
  "max_fee": 500,
  "deadline_hours": 24
}
```

**Fully Refundable (First Class):**
```json
"refund_policy": {
  "allowed": true,
  "fee_percentage": 0,
  "min_fee": 0,
  "max_fee": 0,
  "deadline_hours": 2
}
```

### 2. Change Policy
Controls how booking changes (date/time) are handled.

```json
"change_policy": {
  "allowed": true,              // Can booking be changed?
  "fee": 100,                   // Flat fee for changes (₱)
  "free_within_hours": 24,      // Free changes within X hours of booking
  "max_changes": 3              // Maximum number of changes allowed
}
```

**Examples:**

**Restrictive (Economy Basic):**
```json
"change_policy": {
  "allowed": true,
  "fee": 100,
  "free_within_hours": 0,
  "max_changes": 1
}
```

**Flexible (Business/First):**
```json
"change_policy": {
  "allowed": true,
  "fee": 0,
  "free_within_hours": 0,
  "max_changes": 999
}
```

**24-Hour Free Change (Economy Flex):**
```json
"change_policy": {
  "allowed": true,
  "fee": 50,
  "free_within_hours": 24,
  "max_changes": 2
}
```

### 3. Baggage Allowance
Defines checked and carry-on baggage rules.

```json
"baggage": {
  "checked_bags": 2,            // Number of free checked bags
  "weight_limit_kg": 23,        // Weight limit per checked bag
  "carry_on_allowed": true,     // Is carry-on allowed?
  "carry_on_weight_kg": 7,      // Carry-on weight limit
  "extra_bag_fee": 50           // Fee for additional bags (₱)
}
```

**Examples:**

**Basic (Economy):**
```json
"baggage": {
  "checked_bags": 1,
  "weight_limit_kg": 23,
  "carry_on_allowed": true,
  "carry_on_weight_kg": 7,
  "extra_bag_fee": 75
}
```

**Premium (Business):**
```json
"baggage": {
  "checked_bags": 2,
  "weight_limit_kg": 32,
  "carry_on_allowed": true,
  "carry_on_weight_kg": 10,
  "extra_bag_fee": 50
}
```

**Luxury (First Class):**
```json
"baggage": {
  "checked_bags": 3,
  "weight_limit_kg": 32,
  "carry_on_allowed": true,
  "carry_on_weight_kg": 15,
  "extra_bag_fee": 25
}
```

### 4. Seat Selection
Controls seat selection availability and fees.

```json
"seat_selection": {
  "free": false,                // Is seat selection free?
  "fee": 20,                    // Standard seat selection fee (₱)
  "premium_fee": 50             // Premium seat (exit row, front) fee (₱)
}
```

**Examples:**

**Paid (Economy Basic):**
```json
"seat_selection": {
  "free": false,
  "fee": 15,
  "premium_fee": 40
}
```

**Free (Business/First):**
```json
"seat_selection": {
  "free": true,
  "fee": 0,
  "premium_fee": 0
}
```

### 5. Cancellation Policy
Defines cancellation rules and fees.

```json
"cancellation": {
  "allowed": true,              // Can booking be cancelled?
  "fee": 50,                    // Cancellation fee (₱)
  "free_within_hours": 24,      // Free cancellation within X hours of booking
  "no_refund_hours": 2          // No refund if cancelled within X hours of departure
}
```

**Examples:**

**Restrictive (Economy):**
```json
"cancellation": {
  "allowed": true,
  "fee": 50,
  "free_within_hours": 0,
  "no_refund_hours": 24
}
```

**Flexible (Business):**
```json
"cancellation": {
  "allowed": true,
  "fee": 0,
  "free_within_hours": 0,
  "no_refund_hours": 2
}
```

**24-Hour Free Cancellation:**
```json
"cancellation": {
  "allowed": true,
  "fee": 50,
  "free_within_hours": 24,
  "no_refund_hours": 12
}
```

### 6. Perks
Additional benefits included with the fare.

```json
"perks": {
  "priority_boarding": true,           // Board before general passengers
  "lounge_access": false,              // Access to airport lounge
  "extra_legroom": true,               // Extra legroom seats
  "premium_meals": false,              // Complimentary premium meals
  "amenity_kit": false,                // Toiletry/comfort kit
  "fast_track_security": false         // Fast track through security
}
```

**Examples:**

**Basic (Economy):**
```json
"perks": {
  "priority_boarding": false,
  "lounge_access": false,
  "extra_legroom": false,
  "premium_meals": false,
  "amenity_kit": false,
  "fast_track_security": false
}
```

**Business Class:**
```json
"perks": {
  "priority_boarding": true,
  "lounge_access": false,
  "extra_legroom": true,
  "premium_meals": true,
  "amenity_kit": false,
  "fast_track_security": false
}
```

**First Class:**
```json
"perks": {
  "priority_boarding": true,
  "lounge_access": true,
  "extra_legroom": true,
  "premium_meals": true,
  "amenity_kit": true,
  "fast_track_security": true
}
```

---

## Complete Fare Examples

### Economy Basic (Y)
```json
{
  "refund_policy": {
    "allowed": false,
    "fee_percentage": 0,
    "min_fee": 0,
    "max_fee": 0,
    "deadline_hours": 0
  },
  "change_policy": {
    "allowed": true,
    "fee": 100,
    "free_within_hours": 0,
    "max_changes": 1
  },
  "baggage": {
    "checked_bags": 1,
    "weight_limit_kg": 23,
    "carry_on_allowed": true,
    "carry_on_weight_kg": 7,
    "extra_bag_fee": 75
  },
  "seat_selection": {
    "free": false,
    "fee": 15,
    "premium_fee": 40
  },
  "cancellation": {
    "allowed": true,
    "fee": 50,
    "free_within_hours": 0,
    "no_refund_hours": 24
  },
  "perks": {
    "priority_boarding": false,
    "lounge_access": false,
    "extra_legroom": false,
    "premium_meals": false,
    "amenity_kit": false,
    "fast_track_security": false
  }
}
```

### Business Class (J)
```json
{
  "refund_policy": {
    "allowed": true,
    "fee_percentage": 10,
    "min_fee": 50,
    "max_fee": 500,
    "deadline_hours": 24
  },
  "change_policy": {
    "allowed": true,
    "fee": 0,
    "free_within_hours": 0,
    "max_changes": 999
  },
  "baggage": {
    "checked_bags": 2,
    "weight_limit_kg": 32,
    "carry_on_allowed": true,
    "carry_on_weight_kg": 10,
    "extra_bag_fee": 50
  },
  "seat_selection": {
    "free": true,
    "fee": 0,
    "premium_fee": 0
  },
  "cancellation": {
    "allowed": true,
    "fee": 0,
    "free_within_hours": 0,
    "no_refund_hours": 2
  },
  "perks": {
    "priority_boarding": true,
    "lounge_access": false,
    "extra_legroom": true,
    "premium_meals": true,
    "amenity_kit": false,
    "fast_track_security": false
  }
}
```

### First Class (F)
```json
{
  "refund_policy": {
    "allowed": true,
    "fee_percentage": 0,
    "min_fee": 0,
    "max_fee": 0,
    "deadline_hours": 2
  },
  "change_policy": {
    "allowed": true,
    "fee": 0,
    "free_within_hours": 0,
    "max_changes": 999
  },
  "baggage": {
    "checked_bags": 3,
    "weight_limit_kg": 32,
    "carry_on_allowed": true,
    "carry_on_weight_kg": 15,
    "extra_bag_fee": 25
  },
  "seat_selection": {
    "free": true,
    "fee": 0,
    "premium_fee": 0
  },
  "cancellation": {
    "allowed": true,
    "fee": 0,
    "free_within_hours": 0,
    "no_refund_hours": 1
  },
  "perks": {
    "priority_boarding": true,
    "lounge_access": true,
    "extra_legroom": true,
    "premium_meals": true,
    "amenity_kit": true,
    "fast_track_security": true
  }
}
```

---

## Usage in Code

### Getting Rules
```php
use App\Models\FareRule;

// Get fare rule
$fareRule = FareRule::find(1);

// Get all custom rules
$rules = $fareRule->getCustomRules();

// Get specific rule with dot notation
$refundAllowed = $fareRule->getCustomRule('refund_policy.allowed', false);
$changeFee = $fareRule->getCustomRule('change_policy.fee', 0);
$checkedBags = $fareRule->getCustomRule('baggage.checked_bags', 0);

// Check if lounge access is included
$loungeAccess = $fareRule->getCustomRule('perks.lounge_access', false);
```

### Setting Rules
```php
use App\Models\FareRule;

$fareRule = FareRule::find(1);

// Set custom rules
$fareRule->setCustomRules([
    'refund_policy' => [
        'allowed' => true,
        'fee_percentage' => 5,
        'min_fee' => 25,
        'max_fee' => 250,
        'deadline_hours' => 48
    ],
    'change_policy' => [
        'allowed' => true,
        'fee' => 50,
        'free_within_hours' => 24,
        'max_changes' => 2
    ]
    // ... more rules
]);
```

### Validating Rules
```php
// Example validation logic
$rules = $fareRule->getCustomRules();

if (!isset($rules['refund_policy']['allowed'])) {
    throw new \Exception('Refund policy must be defined');
}

if ($rules['baggage']['checked_bags'] < 0) {
    throw new \Exception('Checked bags cannot be negative');
}
```

---

## Time-Based Rules

### Dynamic Fees Based on Time
Some rules can vary based on time until departure:

```json
{
  "refund_policy": {
    "allowed": true,
    "fee_tiers": [
      { "hours_before": 168, "fee_percentage": 10 },  // 7+ days: 10%
      { "hours_before": 48,  "fee_percentage": 25 },  // 2-7 days: 25%
      { "hours_before": 24,  "fee_percentage": 50 },  // 1-2 days: 50%
      { "hours_before": 0,   "fee_percentage": 100 }  // <24 hours: 100%
    ]
  }
}
```

### Free Cancellation Windows
```json
{
  "cancellation": {
    "allowed": true,
    "free_within_hours": 24,       // Free if within 24h of booking
    "fee_after_hours": 50,         // ₱50 after 24h
    "no_refund_hours": 2           // No refund if <2h to departure
  }
}
```

---

## Rule Priority and Defaults

### Priority Order
1. **rules_json** (highest priority - most flexible)
2. **Database columns** (fallback if JSON missing)
3. **Fare class defaults** (system defaults)

### Accessing Rules
```php
// Try JSON first, fall back to column
$refundAllowed = $fareRule->getCustomRule('refund_policy.allowed')
    ?? $fareRule->is_refundable;

$changeFee = $fareRule->getCustomRule('change_policy.fee')
    ?? $fareRule->change_fee;
```

---

## Validation Rules

### Required Fields
All JSON rules must include these sections:
- `refund_policy.allowed` (boolean)
- `change_policy.allowed` (boolean)
- `baggage.checked_bags` (integer ≥ 0)
- `seat_selection.free` (boolean)
- `cancellation.allowed` (boolean)

### Data Type Validation
```php
// Validation schema
$schema = [
    'refund_policy.allowed' => 'boolean',
    'refund_policy.fee_percentage' => 'numeric|min:0|max:100',
    'change_policy.fee' => 'numeric|min:0',
    'baggage.checked_bags' => 'integer|min:0',
    'baggage.weight_limit_kg' => 'integer|min:0',
    'seat_selection.fee' => 'numeric|min:0',
];
```

---

## Best Practices

### 1. Always Provide Defaults
```json
{
  "refund_policy": {
    "allowed": false,  // Default to most restrictive
    "fee_percentage": 0
  }
}
```

### 2. Be Explicit
Don't rely on implied behavior:
```json
// Bad
"change_policy": {
  "allowed": true
}

// Good
"change_policy": {
  "allowed": true,
  "fee": 0,
  "free_within_hours": 0,
  "max_changes": 999
}
```

### 3. Document Custom Fields
If adding custom fields, document them clearly.

### 4. Validate Before Save
Always validate JSON structure before saving to database.

---

## Migration and Compatibility

### Backward Compatibility
If `rules_json` is null, fall back to database columns:

```php
public function getRefundFee()
{
    // Try JSON first
    $fee = $this->getCustomRule('refund_policy.fee_percentage');
    
    // Fall back to column
    return $fee ?? $this->refund_fee_percentage;
}
```

### Gradual Migration
Existing fare rules without JSON will continue working using database columns.

---

## Future Enhancements

### Possible Additions
- `upgrade_policy` - Rules for upgrading fare class
- `downgrade_policy` - Rules for downgrading
- `companion_policy` - Rules for companion bookings
- `loyalty_perks` - Frequent flyer benefits
- `special_meals` - Meal preference options
- `medical_assistance` - Special assistance rules

### Example:
```json
{
  "upgrade_policy": {
    "allowed": true,
    "fee": 200,
    "availability_based": true
  },
  "loyalty_perks": {
    "points_multiplier": 2,
    "bonus_points": 500,
    "tier_qualifying": true
  }
}
```

---

## Summary

The JSON rules system provides:
- ✅ Flexibility without schema changes
- ✅ Backward compatibility
- ✅ Easy to extend
- ✅ Type-safe with validation
- ✅ Human-readable
- ✅ Version control friendly

**Last Updated:** December 2024  
**Version:** 1.0  
**Status:** Production Ready
