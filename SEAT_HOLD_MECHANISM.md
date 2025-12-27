# Seat Hold Mechanism Documentation

## Overview

The seat hold mechanism allows users to temporarily reserve seats while completing their booking. This prevents other users from booking the same seats during checkout, while also ensuring seats don't remain locked indefinitely.

---

## How It Works

### 1. Hold Creation
When a user begins the booking process:
1. System checks if enough seats are available
2. Creates a `Booking` record with status='held'
3. Locks the requested number of seats
4. Sets 15-minute expiration timer
5. Locks the current price

### 2. Hold States

```
HELD (15 min) → CONFIRMED (payment successful)
     ↓
  EXPIRED (timer runs out)
```

---

## Database Structure

### Bookings Table
```sql
bookings:
  - id
  - booking_reference (e.g., "ABC123")
  - user_id
  - flight_id
  - fare_class_id
  - status (held, confirmed, cancelled, expired)
  - locked_price (price per seat)
  - total_price (locked_price × seat_count)
  - seat_count
  - held_at (timestamp when hold created)
  - hold_expires_at (timestamp when hold expires)
  - confirmed_at
  - cancelled_at
```

### Seats Table
```sql
seats:
  - id
  - flight_id
  - fare_class_id
  - seat_number (e.g., "12A")
  - status (available, held, booked)
  - held_at
  - hold_expires_at
```

---

## BookingHoldService Methods

### Creating a Hold
```php
$booking = $holdService->createHold(
    $user,           // User making the booking
    $flight,         // Flight to book
    $fareClass,      // Economy/Business/First
    2,               // Number of seats
    150.00           // Locked price per seat
);
```

**What happens:**
1. Uses database transaction for atomicity
2. Locks available seats with `lockForUpdate()` (prevents race conditions)
3. Throws exception if not enough seats available
4. Creates booking record
5. Updates seat statuses to 'held'
6. Sets expiration 15 minutes in future

### Confirming a Hold (After Payment)
```php
$holdService->confirmHold($booking);
```

**What happens:**
1. Checks booking is still 'held' status
2. Verifies hold hasn't expired
3. Updates all seats to 'booked' status
4. Updates booking to 'confirmed' status
5. Records confirmation timestamp

### Releasing a Hold (Manual or Auto)
```php
$holdService->releaseHold($booking);
```

**What happens:**
1. Updates all seats back to 'available' status
2. Updates booking to 'expired' status
3. Frees up inventory for other users

### Extending a Hold (Admin Feature)
```php
$holdService->extendHold($booking, 15); // Add 15 more minutes
```

**What happens:**
1. Updates hold_expires_at timestamp
2. Extends seat hold expiration
3. Useful for customer service scenarios

---

## Automatic Expiration

### Scheduled Command
```bash
php artisan bookings:release-expired
```

**Runs every minute via scheduler:**
```php
// In app/Console/Kernel.php
$schedule->command('bookings:release-expired')->everyMinute();
```

**What it does:**
1. Finds all bookings with status='held' where hold_expires_at < now
2. Calls releaseHold() for each
3. Logs the number of holds expired
4. Reports seats released back to inventory

### Manual Testing
```bash
# Dry run (preview without changing anything)
php artisan bookings:release-expired --dry-run

# Verbose output
php artisan bookings:release-expired -v
```

---

## Concurrency Protection

### The Problem
Two users click "Book" on the last available seat at the same time.

### The Solution: Pessimistic Locking
```php
$availableSeats = Seat::where('flight_id', $flight->id)
    ->where('status', 'available')
    ->lockForUpdate()  // <-- This is key
    ->limit($seatCount)
    ->get();
```

**How it works:**
1. `lockForUpdate()` uses MySQL's `SELECT ... FOR UPDATE`
2. First request acquires lock on matching rows
3. Second request waits until first transaction completes
4. Second request sees seats are no longer available
5. Exception thrown: "Not enough seats available"

**Database Query:**
```sql
SELECT * FROM seats 
WHERE flight_id = 15 
  AND status = 'available'
LIMIT 2
FOR UPDATE;
```

---

## Hold Duration

**Default: 15 minutes**

Defined in `BookingHoldService`:
```php
const HOLD_DURATION = 15;
```

**Why 15 minutes?**
- Long enough for user to complete booking
- Short enough to prevent inventory hoarding
- Industry standard for most airlines

---

## Edge Cases Handled

### 1. Not Enough Seats
```php
try {
    $booking = $holdService->createHold($user, $flight, $fareClass, 10, 150.00);
} catch (\Exception $e) {
    // "Not enough seats available. Requested: 10, Available: 3"
    return response()->json(['error' => $e->getMessage()], 400);
}
```

### 2. Hold Expired Before Payment
```php
try {
    $holdService->confirmHold($booking);
} catch (\Exception $e) {
    // "Booking hold has expired"
    // User must search and book again
}
```

### 3. User Has Active Hold
```php
if ($holdService->hasActiveHold($user, $flight)) {
    return redirect()->route('booking.show', $existingBooking)
        ->with('info', 'You already have an active booking for this flight');
}
```

### 4. Race Condition (Last Seat)
```php
// User A and User B both try to book last seat
// Only one succeeds due to lockForUpdate()
// The other receives: "Not enough seats available"
```

---

## Statistics & Monitoring

### Get Hold Statistics
```php
$stats = $holdService->getHoldStatistics();

// Returns:
[
    'active_holds' => 5,          // Current held bookings
    'expired_holds' => 2,         // Waiting to be cleaned up
    'total_held_seats' => 12,     // Seats in 'held' status
    'holds_expiring_soon' => 1,   // Expiring in next 5 minutes
]
```

### Monitoring Active Holds
```sql
-- Find all active holds
SELECT booking_reference, user_id, flight_id, seat_count, 
       TIMESTAMPDIFF(MINUTE, NOW(), hold_expires_at) as minutes_remaining
FROM bookings
WHERE status = 'held' 
  AND hold_expires_at > NOW()
ORDER BY hold_expires_at;
```

### Monitoring Expired But Not Released
```sql
-- Find holds that should be expired
SELECT * FROM bookings
WHERE status = 'held'
  AND hold_expires_at < NOW();
```

---

## User Experience Flow

### Step 1: Search Flights
```
User searches → Sees available seats and current price
```

### Step 2: Select Flight
```
User clicks "Book" → System creates hold
                   → Price locked
                   → Seats locked
                   → 15-minute timer starts
```

### Step 3: Enter Details
```
User fills passenger info
User enters payment details
Timer displays: "Complete booking in 12 minutes"
```

### Step 4A: Payment Success
```
Payment processed → confirmHold() called
                  → Seats become 'booked'
                  → Confirmation email sent
```

### Step 4B: Timer Expires
```
15 minutes pass → Scheduled job runs
                → releaseHold() called
                → Seats become 'available'
                → Booking status = 'expired'
                → User must search again
```

---

## Performance Considerations

### Database Load
- Hold creation: 1 transaction (seats locked atomically)
- Hold confirmation: 1 transaction (seats updated to booked)
- Hold expiration: Batch processed every minute

### Recommended Indexes
```sql
-- Already in migration
INDEX(user_id, status)
INDEX(flight_id, status)
INDEX(hold_expires_at)
```

### Scaling Considerations
For high-traffic scenarios (thousands of concurrent bookings):
- Consider Redis for seat locking (atomic operations)
- Use message queue for hold expiration (vs cron job)
- Implement caching for available seat counts

---

## Testing

### Run Tests
```bash
php artisan test --filter=BookingHoldServiceTest
```

### Test Coverage
1. ✅ Creates hold successfully
2. ✅ Throws exception when not enough seats
3. ✅ Releases hold successfully
4. ✅ Confirms hold successfully
5. ✅ Prevents confirming expired hold
6. ✅ Detects active holds
7. ✅ Calculates remaining time
8. ✅ Extends hold duration
9. ✅ Returns statistics
10. ✅ **Prevents race conditions with pessimistic locking**

---

## Related Documentation
- `INVENTORY_STRATEGY.md` - Physical seats decision
- `PRICING_ALGORITHM.md` - Price locking mechanism
- Phase 3 tasks - Inventory management

---

## Summary

✅ **Seat holds prevent overselling**
✅ **15-minute automatic expiration**
✅ **Pessimistic locking prevents race conditions**
✅ **Price locked at time of hold**
✅ **Scheduled job cleans up expired holds**
✅ **Comprehensive test coverage**

This mechanism ensures a fair, reliable booking system that prevents double-booking while maintaining good user experience.
