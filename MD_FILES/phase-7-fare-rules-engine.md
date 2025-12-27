# Phase 7 - Fare Rules Engine

**Goal:** Decouple business rules from code using a configurable rule system.

---

## What are Fare Rules?
Each fare class (Economy, Business, First) has different rules:
- Refundability (full refund, partial, none)
- Change fees ($0, $50, $200)
- Baggage allowance (1 bag, 2 bags, 3 bags)
- Seat selection (free, paid, included)
- Cancellation penalties

---

## Tasks

### 1. Fare Rules Schema ✅
- [x] Create `fare_rules` table (or add columns to `fare_classes`)
- [x] Fields:
  - `fare_class_id`
  - `is_refundable` (boolean)
  - `change_fee` (decimal)
  - `cancellation_fee` (decimal)
  - `baggage_allowance` (integer)
  - `seat_selection_fee` (decimal, 0 = free)
  - `rules_json` (JSON field for custom rules)

### 2. Rule Definition (JSON Format) ✅
```json
{
  "refund_policy": {
    "allowed": true,
    "fee_percentage": 10,
    "min_fee": 50
  },
  "change_policy": {
    "allowed": true,
    "fee": 100,
    "free_within_hours": 24
  },
  "baggage": {
    "checked_bags": 2,
    "weight_limit_kg": 23
  },
  "seat_selection": {
    "free": false,
    "fee": 20
  }
}
```
- [x] Complete JSON schema documented
- [x] Validation helper created
- [x] Default rules defined
- [x] Examples for all fare classes

### 3. Rule Evaluator Service ✅
- [x] Create `FareRuleService` class
- [x] Method: `canRefund(Booking $booking)` → Returns true/false + fee
- [x] Method: `canChangeBooking(Booking $booking)` → Returns true/false + fee
- [x] Method: `canCancelBooking(Booking $booking)` → Returns true/false + penalty
- [x] Method: `getBaggageAllowance(FareClass $fareClass)` → Returns count + weight

### 4. Rule Application ✅
- [x] When user cancels booking → Apply cancellation rules
- [x] When user requests refund → Apply refund rules
- [x] When user changes booking → Apply change fee rules
- [x] Display rules BEFORE booking (transparency)

### 5. Admin Rule Editor ✅
- [x] Page: `/admin/fare-rules`
- [x] CRUD interface for fare rules
- [x] JSON editor for custom rules (use CodeMirror or similar)
- [x] Preview: "Economy fare rules"

### 6. Rule Validation ✅
- [x] Validate JSON structure before saving
- [x] Ensure required fields exist
- [x] Show error if invalid JSON

### 7. Display Rules to User ✅
- [x] On flight search results, show fare comparison table
- [x] Include: Price, Baggage, Refund, Change policy
- [x] Example:
  ```
  Economy     | $200 | 1 bag  | Non-refundable | $100 change fee
  Business    | $500 | 2 bags | Refundable     | Free changes
  First Class | $800 | 3 bags | Refundable     | Free changes
  ```

### 8. Rule-Based Pricing Adjustments (Optional) ⏭️
- [ ] Premium rules → Higher base fare
- [ ] Restrictive rules → Lower base fare
- [ ] Configurable in admin panel

*Note: Task 8 is optional and can be implemented later if needed.*

---

## Deliverables
- [ ] `fare_rules` table with JSON column
- [ ] `FareRuleService` with rule evaluation methods
- [ ] Admin interface to edit rules
- [ ] Fare comparison table on search results
- [ ] `FARE_RULES.md` - Document rule schema and examples

---

## Example Fare Classes
**Economy (Basic)**
- Non-refundable
- $100 change fee
- 1 checked bag (23kg)
- Paid seat selection ($15)

**Economy (Flexible)**
- Refundable (10% fee)
- Free changes within 24 hours, then $50
- 1 checked bag (23kg)
- Free seat selection

**Business**
- Fully refundable
- Free changes anytime
- 2 checked bags (32kg each)
- Free seat selection + priority boarding

**First Class**
- Fully refundable
- Free changes anytime
- 3 checked bags (32kg each)
- Free seat selection + lounge access

---

## Testing Scenarios
1. User books Economy → Tries to cancel → Pays cancellation fee
2. User books Business → Cancels → Gets full refund
3. Admin edits fare rules → Changes reflected immediately
4. Invalid JSON → System rejects with error message

---

## Advanced: Time-Based Rules
- Refund fee increases as departure date approaches
- Free cancellation >7 days before departure
- Full penalty <24 hours before departure

---

## Next Phase
Once fare rules engine works, move to `phase-8-admin-dashboard.md`
