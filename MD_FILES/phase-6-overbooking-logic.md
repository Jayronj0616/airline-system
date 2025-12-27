# Phase 6 - Overbooking Logic (ADVANCED)

**Goal:** Add airline realism by allowing controlled overbooking.

---

## What is Overbooking?
Airlines intentionally sell more seats than available because 5-15% of passengers typically don't show up (no-shows). This maximizes revenue.

**Risk:** If everyone shows up, airline must deny boarding (compensate passengers).

---

## Tasks

### 1. Overbooking Configuration ✅
- [x] Add `overbooking_enabled` flag to `flights` or global config
- [x] Add `overbooking_percentage` (e.g., 5%, 10%)
- [x] Example: 180-seat plane with 10% overbooking → Allow 198 bookings

### 2. Virtual Capacity Calculation ✅
- [x] Physical capacity: `aircraft.total_seats`
- [x] Virtual capacity: `physical_capacity * (1 + overbooking_percentage / 100)`
- [x] Example: 180 seats × 1.10 = 198 virtual seats

### 3. Inventory Adjustment ✅
- [x] Modify `InventoryService::getAvailableSeats()` to use virtual capacity
- [x] Allow bookings beyond physical capacity up to virtual capacity
- [x] Track `overbooked_count` in `flights` table

### 4. Overbooking Rules ✅
- [x] Enable overbooking only for flights >7 days away
- [x] Disable overbooking 48 hours before departure
- [x] Respect fare class limits (don't overbook Business/First as much)

### 5. No-Show Probability ✅
- [x] Add `no_show_probability` column to `fare_classes` or `fare_rules`
- [x] Economy: 10-15% no-show rate
- [x] Business: 5-8% no-show rate
- [x] First: 2-5% no-show rate
- [x] Use this to determine safe overbooking levels

### 6. Denied Boarding Handling ✅
- [x] If confirmed bookings > physical capacity at departure time:
  - System flags flight as "Overbooked"
  - Admin must manually resolve (volunteer bump or deny boarding)
- [x] Log denied boarding events in `denied_boardings` table

### 7. Admin Controls ✅
- [x] Admin page: `/admin/overbooking`
- [x] Toggle overbooking on/off per flight or globally
- [x] Set overbooking percentage (5%, 10%, 15%)
- [x] View flights at risk of denied boarding

### 8. Safety Thresholds ✅
- [x] Max overbooking: 15% (industry standard)
- [x] Never allow overbooking if flight is <24 hours away
- [x] If `confirmed_bookings > virtual_capacity`, stop accepting bookings

### 9. Reporting ✅
- [x] Calculate "Load Factor" = (Confirmed Bookings / Physical Capacity) × 100%
- [x] Goal: >90% load factor with overbooking
- [x] Track: Revenue gained from overbooking vs denied boarding costs

---

## Deliverables
- [x] Overbooking logic implemented
- [x] Admin controls for overbooking settings
- [x] Safety rules enforced (time-based, max percentage)
- [x] `OVERBOOKING_STRATEGY.md` - Document rules and risks

---

## Example Scenario
```
Flight: Manila → Tokyo (A320, 180 seats)
Departure: 10 days from now
Overbooking: Enabled, 10%
Virtual Capacity: 198 seats

Current Status:
- Confirmed: 185 bookings
- Available: 13 seats (198 - 185)
- Overbooked by: 5 seats (185 - 180)

Expected No-Shows: ~15 passengers (10% avg)
Risk of Denied Boarding: Low
```

---

## Edge Cases
- Flight is 100% sold (180/180), overbooking disabled → Stop accepting bookings
- Flight is 105% sold (189/180), overbooking enabled → Still accepting up to 198
- Flight is 110% sold (198/180), virtual capacity reached → Stop accepting bookings
- Departure is tomorrow → Disable overbooking, stop at physical capacity

---

## Ethical Considerations
- This is a SIMULATION - clearly label that denied boarding won't actually happen
- In real systems, airlines compensate passengers (cash, vouchers, hotel)
- Document this in system overview

---

## Testing Scenarios
1. Enable overbooking → Allow bookings beyond physical capacity
2. Disable overbooking → Stop at physical capacity
3. Flight <48 hours away → Overbooking auto-disabled
4. Simulate high no-show rate → System predicts safe overbooking level

---

## Next Phase
Once overbooking logic works, move to `phase-7-fare-rules-engine.md`
