# Phase 9 - Failure & Edge Case Handling

**Goal:** Prove production readiness by handling failures gracefully.

---

## Critical Failure Scenarios

### 1. Payment Timeout
**Problem:** User starts payment, network fails, payment gateway times out  
**Solution:**
- [ ] Set payment timeout (30 seconds)
- [ ] If timeout → Release seat hold
- [ ] Show error: "Payment failed, please try again"
- [ ] Log payment attempt in `payment_logs` table

### 2. Database Transaction Failure
**Problem:** Booking creation fails mid-transaction (DB crash, connection lost)  
**Solution:**
- [ ] Wrap all booking operations in `DB::transaction()`
- [ ] On failure → Automatic rollback
- [ ] Return user-friendly error
- [ ] Log error for debugging

### 3. Background Job Failure
**Problem:** `demand:simulate` or `bookings:expire` crashes  
**Solution:**
- [ ] Use Laravel's retry mechanism
- [ ] Set max retries: 3 attempts
- [ ] Log failed jobs to `failed_jobs` table
- [ ] Admin alert if job fails repeatedly

### 4. Concurrent Booking Race Condition
**Problem:** Two users book last seat simultaneously  
**Solution:**
- [ ] Use `lockForUpdate()` in inventory check
- [ ] Second user gets error: "Seat no longer available"
- [ ] Test: Simulate 10 concurrent requests for 1 seat

### 5. Seat Hold Expiration During Payment
**Problem:** User holds seat, payment takes 16 minutes, hold expires  
**Solution:**
- [ ] Check if hold is still valid before confirming
- [ ] If expired → Show error, redirect to search
- [ ] Offer to re-book at current price (may be different)

### 6. Price Change During Hold
**Problem:** Should not happen (price is locked), but what if it does?  
**Solution:**
- [ ] Always use `locked_price` from seat hold
- [ ] Never recalculate price during checkout
- [ ] Log discrepancy if detected

### 7. Overbooking Conflict
**Problem:** Virtual capacity reached, but system allows one more booking  
**Solution:**
- [ ] Re-check inventory immediately before confirming
- [ ] Use pessimistic locking
- [ ] Fail gracefully with apology message

### 8. Flight Departure While Booking
**Problem:** Flight departs while user is checking out  
**Solution:**
- [ ] Validate `flight.departure_at > now()` before confirming
- [ ] If departed → Cancel booking, refund (if applicable)
- [ ] Show error: "Flight has already departed"

---

## Tasks

### 1. Error Logging
- [x] Use Laravel's `Log::error()` for all failures
- [x] Create `logs/failures.log` channel
- [x] Log: timestamp, user_id, error message, stack trace

### 2. Retry Logic
- [x] Add retry middleware for API calls (N/A - no external APIs)
- [x] Background jobs: Graceful error handling with logging
- [x] Graceful degradation: If job fails, system still works (continues processing remaining items)

### 3. Transaction Wrappers
- [x] Wrap all critical operations in `DB::transaction()`
- [x] Example:
  ```php
  DB::transaction(function () {
      // Hold seats
      // Create booking
      // Update inventory
  });
  ```

### 4. Idempotency
- [x] Prevent duplicate bookings (use unique constraint)
- [x] Generate `idempotency_key` for payment operations
- [x] If user clicks "Confirm" twice → Only 1 booking created

### 5. User-Friendly Error Messages
- [ ] Never show raw exceptions to users
- [ ] Map errors to friendly messages:
  - DB error → "Something went wrong, please try again"
  - Timeout → "Request timed out, please try again"
  - Seat unavailable → "Seat no longer available, please search again"

### 6. Admin Error Dashboard
- [ ] Page: `/admin/errors`
- [ ] Show recent failures (last 24 hours)
- [ ] Filterable by type (payment, booking, job)
- [ ] Ability to retry failed jobs manually

### 7. Health Checks
- [ ] Endpoint: `/health`
- [ ] Check:
  - Database connection
  - Redis connection (if used)
  - Queue worker status
- [ ] Return: `{ "status": "ok", "database": "connected", "queue": "running" }`

### 8. Rollback Mechanism
- [ ] If booking fails after payment → Auto-refund (mock)
- [ ] If job fails → Roll back changes
- [ ] Log all rollback events

---

## Deliverables
- [ ] All critical operations wrapped in transactions
- [ ] Retry logic implemented for background jobs
- [ ] Idempotency checks in place
- [ ] Error dashboard for admins
- [ ] `FAILURE_SCENARIOS.md` - Document all failure cases and solutions

---

## Testing Scenarios

### Test 1: Payment Timeout
- [ ] Mock payment gateway to timeout
- [ ] Verify seat hold is released
- [ ] Verify user sees error message

### Test 2: Concurrent Bookings
- [ ] Use Apache Bench or Postman Runner
- [ ] Send 10 requests for 1 seat simultaneously
- [ ] Verify only 1 succeeds

### Test 3: Database Failure
- [ ] Kill DB connection mid-transaction
- [ ] Verify rollback occurred
- [ ] Verify no partial data saved

### Test 4: Job Failure
- [ ] Force `demand:simulate` to throw exception
- [ ] Verify job retries 3 times
- [ ] Verify logged in `failed_jobs` table

### Test 5: Hold Expiration
- [ ] Create booking, wait 16 minutes
- [ ] Try to confirm → Should fail
- [ ] Verify seats released

---

## Monitoring (Optional)
- [ ] Set up Laravel Telescope for debugging
- [ ] Use Sentry for error tracking
- [ ] Set up alerts (email/Slack) for critical failures

---

## Next Phase
Once failure handling is solid, move to `phase-10-deployment.md`
