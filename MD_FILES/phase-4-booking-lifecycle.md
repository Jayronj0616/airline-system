# Phase 4 - Booking Lifecycle Flow

**Goal:** Model the full booking state machine and transitions.

---

## Booking States

```
SEARCH → HELD → CONFIRMED → CANCELLED
          ↓
       EXPIRED
```

---

## Tasks

### 1. Booking State Machine ✅
- [x] Define enum or constants for booking states:
  - `HELD` - User has started booking, seats locked
  - `CONFIRMED` - Payment successful, booking complete
  - `CANCELLED` - User or admin cancelled
  - `EXPIRED` - Hold timeout, seats released
- [x] Add `status` column to `bookings` table
- [x] Add timestamps: `held_at`, `confirmed_at`, `cancelled_at`, `expired_at`

**Implementation:** Already complete from Phase 3. Database schema includes enum status column with all 4 states, all required timestamps with proper casts, booking reference auto-generation, and optimized indexes. See `PHASE_4_COMPLETE.md` Task 1 for details.

### 2. State Transitions ✅
- [x] Implement `Booking::hold()` - Creates booking with HELD status
- [x] Implement `Booking::confirm()` - Moves HELD → CONFIRMED
- [x] Implement `Booking::cancel()` - Moves CONFIRMED → CANCELLED
- [x] Implement `Booking::expire()` - Moves HELD → EXPIRED
- [x] Prevent invalid transitions (e.g., EXPIRED → CONFIRMED)

**Implementation:** Added state transition validation to all methods. Guard clauses prevent invalid transitions with clear error messages. Added helper methods: isHeld(), isConfirmed(), isCancelled(), isExpired(), canBeConfirmed(), canBeExpired(). Created 23 comprehensive tests. See `PHASE_4_COMPLETE.md` Task 2 for details.

### 3. Booking Controller ✅
- [x] `store()` - Create booking (HELD status)
- [x] `confirm()` - Confirm payment (HELD → CONFIRMED)
- [x] `cancel()` - Cancel booking (CONFIRMED → CANCELLED)
- [x] `show()` - View booking details

**Implementation:** Controller fully functional from Phase 3. Refactored to use Task 2 state methods (isHeld(), isConfirmed(), etc.), added exception handling for concurrent state transitions, maintains authorization on all routes. Created 18 comprehensive feature tests. See `PHASE_4_COMPLETE.md` Task 3 for details.

### 4. Price Freezing ✅
- [x] When booking is HELD, store `locked_price` in `bookings` table
- [x] Price cannot change during hold period (15 min)
- [x] If hold expires and user retries, fetch new price

**Implementation:** Fully implemented in Phase 3. Price locked at hold creation via `PricingService::calculateCurrentPrice()`, stored as DECIMAL(10,2), remains constant during 15-min hold, protects against demand/time/inventory price increases. Expired holds require new price calculation. Created 10 comprehensive tests. See `PHASE_4_COMPLETE.md` Task 4 for details.

### 5. Seat Release Logic ✅
- [x] When booking → CANCELLED: Release seats back to inventory
- [x] When booking → EXPIRED: Release seats back to inventory
- [x] Create `BookingService::releaseSeats(Booking $booking)` method

**Implementation:** Fully implemented in Phase 3. Seats automatically released via `expire()` and `cancel()` methods, loop through passengers and call `seat->release()`, transaction-safe, handles multiple seats atomically. Created 10 comprehensive tests. See `PHASE_4_COMPLETE.md` Task 5 for details.

### 6. Booking Expiration Job ✅
- [x] Command: `php artisan bookings:release-expired`
- [x] Find all bookings where `status = HELD` and `expires_at < now()`
- [x] Call `Booking::expire()` for each
- [x] Schedule to run every minute

**Implementation:** Fully implemented in Phase 3. Command `ReleaseExpiredHolds` with dry-run support, scheduled in Kernel to run every minute, uses `expiredHolds()` scope, progress bar and statistics, verbose mode. Created 10 comprehensive tests. See `PHASE_4_COMPLETE.md` Task 6 for details.

### 7. Booking Confirmation Flow ✅
- [x] **Page 1:** Flight search results
- [x] **Page 2:** Select fare class, enter passenger count → Create HELD booking
- [x] **Page 3:** Passenger details form (name, email, etc.)
- [x] **Page 4:** Payment (mock) → Confirm booking
- [x] **Page 5:** Confirmation screen with booking reference

**Implementation:** Complete 5-page booking flow with real-time countdown timers, comprehensive error handling, and seamless state transitions. See `PHASE_4_TASK_7_8_9_SUMMARY.md` for full details.

**Key Features:**
- Flight search with filters (origin, destination, date)
- Fare class comparison with real-time pricing
- Dynamic passenger information forms
- 15-minute countdown timer with visual warnings
- Mock payment processing (credit/debit/PayPal)
- Professional confirmation page with booking summary
- Automatic seat assignment
- Duplicate booking prevention
- Transaction-safe operations

### 8. Booking Reference Generation ✅
- [x] Generate unique booking reference (e.g., `ABC123XYZ`)
- [x] Format: 6-9 alphanumeric characters
- [x] Store in `bookings.reference` column

**Implementation:** Enhanced booking reference generation in `Booking::generateBookingReference()`. Generates 6-9 character alphanumeric codes with both letters and numbers required. Excludes confusing characters (I, O, 0, 1). Ensures uniqueness via database check. Auto-generates on booking creation. See `PHASE_4_TASK_7_8_9_SUMMARY.md` for full details.

**Example References:** `ABC123XY`, `DEF456ZW9`, `GH7J8K`, `MNP2QR3ST4`

### 9. Email Notifications ✅
- [x] Send confirmation email on CONFIRMED
- [x] Send cancellation email on CANCELLED
- [x] Use Laravel Mail with Mailtrap for testing

**Implementation:** Professional email notification system with two Mailable classes and HTML email templates. Emails sent automatically on booking confirmation and cancellation. Includes booking reference, flight details, passenger information, and payment summary. Error handling ensures booking succeeds even if email fails. See `PHASE_4_TASK_7_8_9_SUMMARY.md` for full details.

**Email Types:**
1. **Booking Confirmed** - Professional green-themed email with complete booking details
2. **Booking Cancelled** - Red-themed email with cancellation reason and refund information

---

## Deliverables
- [x] State machine implemented and documented
- [x] Booking controller with all CRUD operations
- [x] Price freezing works during hold period
- [x] Expired bookings auto-release seats
- [x] Booking flow tested end-to-end
- [x] Email notifications functional
- [x] `BOOKING_FLOW.md` - Document state transitions (See PHASE_4_TASK_7_8_9_SUMMARY.md)

---

## Edge Cases
- [x] User refreshes payment page → Don't create duplicate booking (Handled via existing booking check)
- [x] User clicks "Confirm" twice → Idempotency check (State transition validation prevents)
- [x] Booking expires while user is on payment page → Show error, redirect to search (Timer + server-side check)

---

## Validation Rules
- [x] Cannot confirm expired booking (State validation in `confirm()`)
- [x] Cannot cancel already cancelled booking (State validation in `cancel()`)
- [x] Cannot hold seats if not enough inventory (Capacity check before hold)
- [x] Passenger count must match booking (Validated in `storePassengers()`)

---

## Testing Scenarios
1. [x] **Happy path:** Search → Hold → Confirm → Success (Fully functional)
2. [x] **Expiration:** Search → Hold → Wait 16 minutes → Booking expired (Auto-release via cron)
3. [x] **Cancellation:** Confirmed booking → User cancels → Seats released (Email sent)
4. [x] **Idempotency:** User double-clicks confirm → Only 1 booking created (State guards prevent)

---

## Phase 4 Status: COMPLETE ✅

All tasks (1-9) have been successfully implemented and tested. The booking lifecycle is fully functional with:
- State machine with proper transitions
- Complete 5-page booking flow
- Enhanced booking reference generation
- Professional email notifications
- Comprehensive error handling
- Real-time countdown timers
- Transaction-safe operations

---

## Next Phase
Move to `phase-5-demand-simulation.md` to implement demand modeling and dynamic pricing adjustments.
