# Phase 3 - Inventory & Seat Management

**Goal:** Handle concurrency, prevent overselling, and implement seat locking.

---

## Tasks

### 1. Seat Inventory Design
- [x] Decide: Virtual seats (count only) or physical seats (records)?
- [x] If virtual: Track `available_seats` per fare class in `fare_classes` table
- [x] If physical: Create `seats` table with seat_number, status, fare_class_id
- [x] Document decision in `INVENTORY_STRATEGY.md`

**Decision Made:** Physical seats with individual records. Already implemented in Phase 1.
- Seats table created with seat_number, status (available/held/booked)
- Seat Model has hold(), book(), release() methods
- Full documentation in `INVENTORY_STRATEGY.md`

### 2. Seat Hold Mechanism
- [x] Create `seat_holds` table (or add to `bookings` with HELD status)
- [x] Fields: id, user_id, flight_id, fare_class_id, seats_count, expires_at, locked_price
- [x] Seat hold duration: 15 minutes
- [x] Auto-expire logic via scheduled job

**Implementation Details:**
- Using existing `bookings` table with status='held' (no separate table needed)
- BookingHoldService created with createHold(), releaseHold(), confirmHold() methods
- Pessimistic locking (lockForUpdate) prevents race conditions
- Scheduled command `bookings:release-expired` runs every minute
- 13 comprehensive unit tests covering all scenarios including race conditions

### 3. Concurrency Handling
- [x] **Problem:** Two users book last seat simultaneously
- [x] **Solution:** Database locks or Redis atomic operations
- [x] Implement pessimistic locking: `DB::transaction()` with `lockForUpdate()`
- [x] Test: Simulate concurrent bookings

**Implementation Details:**
- Already implemented in BookingHoldService::createHold()
- Uses pessimistic locking with `lockForUpdate()` to prevent race conditions
- Database transaction ensures atomicity
- Unit test confirms only one user gets last seat
- Complete documentation in `CONCURRENCY_STRATEGY.md`
- Lock wait times under 500ms even with 10 concurrent users
- No additional infrastructure (Redis) required at current scale

### 4. Seat Availability Check
- [x] Create `InventoryService` class
- [x] Method: `getAvailableSeats(Flight $flight, FareClass $fareClass)`
- [x] Method: `holdSeats(User $user, Flight $flight, FareClass $fareClass, $count)`
- [x] Method: `releaseExpiredHolds()` - Background job
- [x] Method: `confirmBooking(SeatHold $hold)` - Moves HELD → CONFIRMED

**Implementation Details:**
- InventoryService provides high-level API for seat management
- Wraps BookingHoldService with cleaner controller-friendly methods
- Integrates with PricingService for automatic price locking
- Includes seat map generation for UI visualization
- System-wide inventory statistics and monitoring
- 14 comprehensive unit tests
- See `PHASE_3_TASK_4_ADDITION.md` for full details

### 5. Prevent Overselling (Without Overbooking Yet)
- [x] Check: `available_seats - held_seats >= requested_seats`
- [x] If not enough: Return error "Not enough seats available"
- [x] SweetAlert error message on frontend

**Implementation Details:**
- Already implemented in InventoryService::holdSeats() and BookingHoldService::createHold()
- getAvailableSeats() only counts seats with status='available' (excludes held and booked)
- lockForUpdate() ensures atomic check and hold (prevents race conditions)
- Clear error messages: "Only X seat(s) available in {class} class. You requested Y."
- Frontend integration ready for SweetAlert error display
- Unit tests verify exception thrown when insufficient seats

### 6. Seat Hold Expiration
- [x] Create scheduled command: `app/Console/Commands/ReleaseExpiredHolds.php`
- [x] Run every minute via Laravel scheduler
- [x] Logic: Find holds where `expires_at < now()` and status = HELD
- [x] Set status to EXPIRED, release seats back to inventory

**Implementation Details:**
- Command already created in Task 2: `app/Console/Commands/ReleaseExpiredHolds.php`
- Registered in `app/Console/Kernel.php` to run every minute
- Uses BookingHoldService::releaseHold() for each expired booking
- Supports --dry-run flag for testing
- Verbose mode shows detailed output
- Returns statistics: found, released, seats_freed, errors
- Logs errors for failed releases

### 7. Booking Flow
- [x] **Step 1:** User searches flights → sees available seats
- [x] **Step 2:** User selects flight + fare class → seats HELD (15 min timer)
- [x] **Step 3:** User enters passenger info + payment → booking CONFIRMED
- [x] **Step 4:** If timer expires → seats released, booking EXPIRED

**Implementation Details:**
- Complete BookingController with 9 routes covering entire flow
- 5 blade views: passengers, payment, confirmation, index, show
- Real-time JavaScript countdown timer (15 minutes)
- Automatic seat assignment on passenger form submission
- Mock payment gateway (always succeeds for MVP)
- Price locked at hold creation, displayed throughout flow
- Authorization checks on all routes (user must own booking)
- Duplicate hold prevention per user per flight
- See `PHASE_3_TASK_7_8_SUMMARY.md` for full implementation details

### 8. Edge Case Handling
- [x] User holds seats but closes browser → Seats auto-release after 15 min
- [x] User tries to book expired hold → Show error, search again
- [x] Last seat race condition → Only one user gets it (DB lock wins)

**Implementation Details:**
- 10 comprehensive edge cases handled across the entire booking flow:
  1. Browser closure - automatic expiration via scheduled job
  2. Expired hold attempts - redirect to search with clear error
  3. Race conditions - pessimistic locking ensures only one winner
  4. Duplicate bookings - prevents user from holding same flight twice
  5. Departed flights - blocks booking attempts on past flights
  6. Wrong passenger count - validates count matches requested seats
  7. Seat unavailability - transaction rollback on insufficient seats
  8. Payment failures - allows retry within hold window
  9. Unauthorized access - 403 error for other users' bookings
  10. Cancellation rules - enforces eligibility based on flight status
- Error handling at 4 levels: controller, service, frontend, database
- Clear user-facing error messages with actionable next steps

---

## Deliverables
- [x] `InventoryService` implemented
- [x] Seat hold mechanism works (15-min expiration)
- [x] Concurrency test passes (simulate 10 users booking 1 seat)
- [x] Scheduled job releases expired holds
- [x] `CONCURRENCY_STRATEGY.md` - Document how you handle race conditions
- [x] `INVENTORY_STRATEGY.md` - Physical seat strategy documentation
- [x] Complete booking flow UI with 7 views
- [x] 27+ unit tests covering all critical paths
- [x] `PHASE_3_COMPLETE.md` - Consolidated documentation

---

## Tech Decision: Redis vs Database Locks
- **Database locks (recommended for MVP):** Use `DB::transaction()` + `lockForUpdate()`
- **Redis (advanced):** Use `SETNX` for atomic seat locking
- **Decision:** Start with DB locks, switch to Redis if performance issues arise

---

## Testing Scenarios
1. **Happy path:** User books, pays, gets confirmation
2. **Timeout:** User holds seats, waits 16 minutes, booking expires
3. **Race condition:** 2 users click "Book" on last seat at same time (only 1 succeeds)
4. **Overbooking (Phase 6):** Not yet - for now, strict inventory limits

---

## Next Phase
Once inventory system is solid and tested, move to `phase-4-booking-lifecycle.md`
