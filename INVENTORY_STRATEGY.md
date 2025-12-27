# Inventory Strategy Documentation

## Decision: Physical Seats (Record-Based)

**Status:** ✅ IMPLEMENTED IN PHASE 1

---

## Overview

The airline system uses a **physical seat inventory model** where each seat is a separate database record with a unique seat number (e.g., "12A", "15C"). This was implemented during Phase 1 domain modeling.

---

## Why Physical Seats?

### Advantages
1. **Seat Selection** - Passengers can choose specific seats (12A, 15C, etc.)
2. **Seat Maps** - Can display visual seat maps showing availability
3. **Audit Trail** - Every seat has its own history and status
4. **Flexibility** - Can handle complex scenarios like blocked seats, wheelchair seats, etc.
5. **Real-World Accuracy** - Mirrors actual airline operations

### Trade-offs
1. **More Records** - A 180-seat plane creates 180 records per flight
2. **Higher Database Load** - More rows to query and update
3. **Complexity** - More logic needed for seat management

---

## Database Structure

### Seats Table
```sql
CREATE TABLE seats (
    id BIGINT PRIMARY KEY,
    flight_id BIGINT,
    fare_class_id BIGINT,
    seat_number VARCHAR(5),  -- e.g., "12A", "15C"
    status ENUM('available', 'held', 'booked'),
    held_at TIMESTAMP,
    hold_expires_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE(flight_id, seat_number),
    INDEX(flight_id, fare_class_id, status)
);
```

### Seat Statuses
- **available** - Open for booking
- **held** - Reserved during checkout (15-minute timer)
- **booked** - Confirmed booking, passenger assigned

---

## Seat Lifecycle

```
┌─────────────┐
│  available  │ ◄─── Initial state when flight created
└──────┬──────┘
       │
       │ User selects seat
       ▼
  ┌─────────┐
  │  held   │ ◄─── 15-minute hold during checkout
  └────┬────┘
       │
       ├─────► Payment successful ────► booked
       │
       └─────► Timer expires ────────► available
```

---

## Implementation Details

### Seat Model Methods

**Hold Management:**
```php
$seat->hold($minutes = 15);         // Hold seat for booking
$seat->isHoldExpired();             // Check if hold expired
$seat->releaseExpiredHold();        // Auto-release if expired
```

**Booking Actions:**
```php
$seat->book();                      // Confirm booking
$seat->release();                   // Cancel/refund
```

**Query Scopes:**
```php
Seat::available()->get();           // Get available seats
Seat::held()->get();                // Get held seats
Seat::booked()->get();              // Get booked seats
```

### Flight Model Methods

**Seat Queries:**
```php
$flight->availableSeatsForFareClass($fareClassId);  // Count available
$flight->seats()->where('status', 'available');     // Query builder
```

---

## Seat Numbering Convention

### Format
`{ROW_NUMBER}{COLUMN_LETTER}`

### Examples
- `1A` - First class, row 1, aisle
- `12C` - Economy, row 12, middle
- `25F` - Economy, row 25, window

### Typical Layout (Boeing 737)
```
Rows 1-3:   First Class    (A-D)      = 12 seats
Rows 4-9:   Business Class (A-F)      = 36 seats
Rows 10-35: Economy Class  (A-F)      = 156 seats
```

---

## Seat Creation Strategy

Seats are automatically created when a flight is seeded:

```php
// FlightSeeder creates all seats for each flight
foreach ($flights as $flight) {
    // First class: rows 1-3
    for ($row = 1; $row <= 3; $row++) {
        foreach (['A', 'B', 'C', 'D'] as $col) {
            Seat::create([
                'flight_id' => $flight->id,
                'fare_class_id' => $firstClass->id,
                'seat_number' => "{$row}{$col}",
                'status' => 'available'
            ]);
        }
    }
    
    // Business: rows 4-9
    // Economy: rows 10-35
    // ... etc
}
```

---

## Performance Considerations

### Query Optimization
- **Index on `(flight_id, fare_class_id, status)`** - Fast availability checks
- **Index on `flight_id, seat_number`** - Fast seat lookups
- **Unique constraint** - Prevents duplicate seat numbers

### Typical Query Performance
```php
// Fast: Uses index
$available = Seat::where('flight_id', $flightId)
    ->where('fare_class_id', $fareClassId)
    ->where('status', 'available')
    ->count();

// Result: ~5-10ms for 180 seats
```

### Seat Count Statistics
- Small plane (ATR 72): ~72 seats per flight
- Medium plane (Boeing 737): ~180 seats per flight
- Large plane (Boeing 777): ~350 seats per flight

For 20 flights in database:
- Small fleet: ~1,440 seat records
- Medium fleet: ~3,600 seat records
- Large fleet: ~7,000 seat records

**Verdict:** Well within MySQL performance limits.

---

## Hold Expiration Strategy

### Automatic Cleanup
Expired holds are released in two ways:

1. **On-Demand Check** (when seat is accessed)
```php
$seat->releaseExpiredHold();
```

2. **Scheduled Job** (every 5 minutes)
```bash
php artisan schedule:run
```

```php
// In Console/Kernel.php
$schedule->command('seats:release-expired')->everyFiveMinutes();
```

---

## Alternative: Virtual Seats (Not Used)

We considered but rejected a virtual seat model:

```sql
-- Virtual seats table (NOT IMPLEMENTED)
CREATE TABLE fare_class_inventory (
    flight_id BIGINT,
    fare_class_id BIGINT,
    available_seats INT,
    booked_seats INT,
    held_seats INT
);
```

**Why we didn't use it:**
- Can't do seat selection (passenger can't pick "12A")
- Harder to track individual bookings
- No seat map visualization
- Less realistic for an airline system

**When virtual seats make sense:**
- Hotel rooms (less important which specific room)
- Event tickets (general admission)
- Parking spots (any spot is fine)

---

## Next Steps (Phase 3 Continuation)

Now that seat inventory is documented, next implement:

1. **Seat Holding Service** - Business logic for holds
2. **Hold Expiration Job** - Scheduled cleanup of expired holds
3. **Seat Selection UI** - Visual seat map for passengers
4. **Admin Seat Management** - Block/unblock seats, change status

---

## Related Documentation
- `DATABASE_SCHEMA.md` - Full database design
- `phase-1-domain-modeling.md` - Original seat design decisions
- Phase 3 tasks for seat management implementation

---

## Summary

✅ **Decision: Physical seats with individual records**
✅ **Implemented in Phase 1**
✅ **Seats table created with status tracking**
✅ **Seat Model with hold/book/release methods**
✅ **Ready for Phase 3 booking implementation**

This strategy provides the flexibility needed for a realistic airline booking system while maintaining good performance.
