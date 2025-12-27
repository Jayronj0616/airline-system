# Phase 1 - Domain Modeling & Data Design

**Goal:** Understand airline business logic and design the database schema.

---

## Tasks

### 1. Core Entity Design
- [x] Design `flights` table schema
- [x] Design `aircraft` table schema
- [x] Design `seats` table schema (physical seats with seat numbers)
- [x] Design `fare_classes` table schema
- [x] Design `fare_rules` table schema
- [x] Design `bookings` table schema
- [x] Design `passengers` table schema
- [x] Design `price_history` table schema

### 2. Relationship Mapping
- [x] Map Flight → Aircraft relationship
- [x] Map Flight → FareClass relationship
- [x] Map Booking → Passenger relationship
- [x] Map Booking → Flight relationship
- [x] Map Seat → Booking → Passenger relationship
- [x] Document cascade delete rules

### 3. Key Business Logic Decisions
- [x] **Physical seats with seat numbers** - Allows seat selection, one record per seat
- [x] **Overbooking strategy** - Will implement in Phase 6
- [x] **Seat assignment** - At booking time (seat selection)
- [x] **Price locking** - Stored in bookings table when hold is created
- [x] **Refund handling** - Based on fare rules per fare class
- [x] **Booking states** - HELD → CONFIRMED → CANCELLED + EXPIRED

### 4. Migration Creation
- [x] Create `aircraft` migration
- [x] Create `flights` migration
- [x] Create `fare_classes` migration
- [x] Create `fare_rules` migration
- [x] Create `bookings` migration
- [x] Create `passengers` migration
- [x] Create `price_history` migration
- [x] Create `seats` migration

### 5. Model Creation
- [x] Create `Aircraft` model with relationships
- [x] Create `Flight` model with relationships
- [x] Create `FareClass` model with relationships
- [x] Create `FareRule` model with relationships
- [x] Create `Booking` model with relationships
- [x] Create `Passenger` model with relationships
- [x] Create `PriceHistory` model with relationships
- [x] Create `Seat` model with relationships

### 6. Seeders
- [x] Create `AircraftSeeder` - Seed 4 aircraft types
- [x] Create `FlightSeeder` - Seed 20 sample flights with physical seats
- [x] Create `FareClassSeeder` - Seed Economy/Business/First
- [x] Create `FareRuleSeeder` - Seed rules per fare class

---

## Deliverables
- [x] ER diagram (included in DATABASE_SCHEMA.md)
- [x] All migrations created
- [x] All models with relationships defined
- [x] Database ready to be seeded
- [x] `DATABASE_SCHEMA.md` - Complete documentation of all tables

---

## Key Design Decisions Made

### 1. Physical Seats (Not Virtual)
- Each seat is a separate database record
- Seat numbers: 1A, 1B, 12C, etc.
- Enables seat selection feature
- Status: available → held → booked

### 2. Price Locking Strategy
- Price locked in `bookings.locked_price` when hold is created
- Survives server restarts (not cached)
- Never changes during 15-minute hold period

### 3. Booking Flow (4 States)
```
HELD (15 min) → CONFIRMED (payment)
      ↓
   EXPIRED (timeout)
      ↓
  CANCELLED (user action)
```

### 4. Seat Creation Strategy
- Seeds create all physical seats for each flight
- Economy: rows 10+, Business: rows 4-9, First: rows 1-3
- 6 seats per row (A-F columns)
- Generated automatically when flight is seeded

---

## Database Statistics (After Seeding)
- **Aircraft:** 4 models
- **Flights:** 20 flights
- **Seats:** ~3,600 seats (180 seats × 20 flights avg)
- **Fare Classes:** 3 (Economy, Business, First)
- **Fare Rules:** 3 (one per class)

---

## Next Steps

### Run These Commands:
```bash
# Run all migrations
php artisan migrate

# Seed the database
php artisan db:seed
```

### Verify Data:
```sql
-- Check aircraft
SELECT * FROM aircraft;

-- Check flights
SELECT flight_number, origin, destination, departure_time FROM flights;

-- Check seat counts per flight
SELECT 
    f.flight_number,
    fc.name as fare_class,
    COUNT(s.id) as seat_count,
    SUM(CASE WHEN s.status = 'available' THEN 1 ELSE 0 END) as available
FROM flights f
JOIN seats s ON s.flight_id = f.id
JOIN fare_classes fc ON fc.id = s.fare_class_id
GROUP BY f.flight_number, fc.name
ORDER BY f.flight_number, fc.name;
```

---

## Next Phase
Once database is migrated and seeded, move to `phase-2-pricing-engine.md`
