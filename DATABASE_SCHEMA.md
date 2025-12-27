# Database Schema Documentation

## Overview
This document describes the database structure for the Airline Revenue Management System.

---

## Tables

### 1. users
**Purpose:** Stores user accounts (passengers and admins)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO | Primary key |
| name | VARCHAR(255) | NO | - | User's full name |
| email | VARCHAR(255) | NO | - | User's email (unique) |
| email_verified_at | TIMESTAMP | YES | NULL | Email verification timestamp |
| password | VARCHAR(255) | NO | - | Hashed password |
| role | ENUM('passenger', 'admin') | NO | 'passenger' | User role |
| remember_token | VARCHAR(100) | YES | NULL | Remember token |
| created_at | TIMESTAMP | YES | NULL | Record creation time |
| updated_at | TIMESTAMP | YES | NULL | Record update time |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (email)

---

### 2. aircraft
**Purpose:** Stores aircraft models and their seat configurations

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO | Primary key |
| model | VARCHAR(100) | NO | - | Aircraft model name |
| code | VARCHAR(20) | NO | - | IATA aircraft code (unique) |
| total_seats | INT UNSIGNED | NO | - | Total number of physical seats |
| economy_seats | INT UNSIGNED | NO | - | Number of economy seats |
| business_seats | INT UNSIGNED | NO | - | Number of business seats |
| first_class_seats | INT UNSIGNED | NO | - | Number of first class seats |
| created_at | TIMESTAMP | YES | NULL | Record creation time |
| updated_at | TIMESTAMP | YES | NULL | Record update time |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (code)

**Business Rules:**
- total_seats = economy_seats + business_seats + first_class_seats

---

### 3. fare_classes
**Purpose:** Defines fare classes (Economy, Business, First)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO | Primary key |
| name | VARCHAR(50) | NO | - | Fare class name |
| code | VARCHAR(10) | NO | - | Fare class code (unique) |
| description | TEXT | YES | NULL | Fare class description |
| created_at | TIMESTAMP | YES | NULL | Record creation time |
| updated_at | TIMESTAMP | YES | NULL | Record update time |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (code)

**Fixed Values:**
- Economy (Y)
- Business (J)
- First Class (F)

---

### 4. fare_rules
**Purpose:** Stores rules and policies for each fare class

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO | Primary key |
| fare_class_id | BIGINT UNSIGNED | NO | - | Foreign key to fare_classes |
| is_refundable | BOOLEAN | NO | false | Can booking be refunded? |
| refund_fee_percentage | DECIMAL(5,2) | NO | 0 | Refund fee (0-100%) |
| change_fee | DECIMAL(10,2) | NO | 0 | Flat fee for changes |
| checked_bags_allowed | INT UNSIGNED | NO | 0 | Number of checked bags |
| bag_weight_limit_kg | INT UNSIGNED | NO | 0 | Weight limit per bag (kg) |
| seat_selection_free | BOOLEAN | NO | false | Is seat selection free? |
| priority_boarding | BOOLEAN | NO | false | Priority boarding included? |
| cancellation_policy | TEXT | YES | NULL | Human-readable policy |
| created_at | TIMESTAMP | YES | NULL | Record creation time |
| updated_at | TIMESTAMP | YES | NULL | Record update time |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (fare_class_id) → fare_classes(id) ON DELETE CASCADE

---

### 5. flights
**Purpose:** Stores flight information

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO | Primary key |
| flight_number | VARCHAR(10) | NO | - | Flight number (unique) |
| aircraft_id | BIGINT UNSIGNED | NO | - | Foreign key to aircraft |
| origin | VARCHAR(3) | NO | - | Origin airport code |
| destination | VARCHAR(3) | NO | - | Destination airport code |
| departure_time | DATETIME | NO | - | Scheduled departure |
| arrival_time | DATETIME | NO | - | Scheduled arrival |
| status | ENUM | NO | 'scheduled' | Flight status |
| base_price_economy | INT UNSIGNED | NO | 100 | Base price for economy |
| base_price_business | INT UNSIGNED | NO | 300 | Base price for business |
| base_price_first | INT UNSIGNED | NO | 800 | Base price for first class |
| demand_score | DECIMAL(5,2) | NO | 50 | Demand score (0-100) |
| created_at | TIMESTAMP | YES | NULL | Record creation time |
| updated_at | TIMESTAMP | YES | NULL | Record update time |

**Status Values:**
- scheduled
- boarding
- departed
- arrived
- cancelled

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (flight_number)
- FOREIGN KEY (aircraft_id) → aircraft(id) ON DELETE RESTRICT
- INDEX (origin, destination, departure_time)
- INDEX (departure_time)

---

### 6. seats
**Purpose:** Stores individual seat records for each flight

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO | Primary key |
| flight_id | BIGINT UNSIGNED | NO | - | Foreign key to flights |
| fare_class_id | BIGINT UNSIGNED | NO | - | Foreign key to fare_classes |
| seat_number | VARCHAR(5) | NO | - | Seat number (e.g., "12A") |
| status | ENUM | NO | 'available' | Seat status |
| held_at | TIMESTAMP | YES | NULL | When seat was held |
| hold_expires_at | TIMESTAMP | YES | NULL | When hold expires |
| created_at | TIMESTAMP | YES | NULL | Record creation time |
| updated_at | TIMESTAMP | YES | NULL | Record update time |

**Status Values:**
- available
- held
- booked

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (flight_id) → flights(id) ON DELETE CASCADE
- FOREIGN KEY (fare_class_id) → fare_classes(id) ON DELETE RESTRICT
- UNIQUE KEY (flight_id, seat_number)
- INDEX (flight_id, fare_class_id, status)

**Business Rules:**
- Seat numbers must be unique per flight
- Holds expire after 15 minutes
- Status transitions: available → held → booked OR held → available (expired)

---

### 7. bookings
**Purpose:** Stores booking information

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO | Primary key |
| booking_reference | VARCHAR(10) | NO | - | Unique booking reference |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| flight_id | BIGINT UNSIGNED | NO | - | Foreign key to flights |
| fare_class_id | BIGINT UNSIGNED | NO | - | Foreign key to fare_classes |
| status | ENUM | NO | 'held' | Booking status |
| locked_price | DECIMAL(10,2) | NO | - | Price per seat (locked) |
| total_price | DECIMAL(10,2) | NO | - | Total price for all seats |
| seat_count | INT UNSIGNED | NO | - | Number of seats booked |
| held_at | TIMESTAMP | YES | NULL | When booking was held |
| hold_expires_at | TIMESTAMP | YES | NULL | When hold expires |
| confirmed_at | TIMESTAMP | YES | NULL | When booking confirmed |
| cancelled_at | TIMESTAMP | YES | NULL | When booking cancelled |
| cancellation_reason | TEXT | YES | NULL | Reason for cancellation |
| created_at | TIMESTAMP | YES | NULL | Record creation time |
| updated_at | TIMESTAMP | YES | NULL | Record update time |

**Status Values:**
- held
- confirmed
- cancelled
- expired

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (booking_reference)
- FOREIGN KEY (user_id) → users(id) ON DELETE CASCADE
- FOREIGN KEY (flight_id) → flights(id) ON DELETE RESTRICT
- FOREIGN KEY (fare_class_id) → fare_classes(id) ON DELETE RESTRICT
- INDEX (user_id, status)
- INDEX (flight_id, status)
- INDEX (hold_expires_at)

**Business Rules:**
- Booking reference auto-generated (6 chars, uppercase)
- Holds expire after 15 minutes
- Price locked at time of booking
- Status flow: held → confirmed OR held → expired

---

### 8. passengers
**Purpose:** Stores passenger information for each booking

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO | Primary key |
| booking_id | BIGINT UNSIGNED | NO | - | Foreign key to bookings |
| seat_id | BIGINT UNSIGNED | NO | - | Foreign key to seats |
| first_name | VARCHAR(100) | NO | - | Passenger first name |
| last_name | VARCHAR(100) | NO | - | Passenger last name |
| email | VARCHAR(150) | NO | - | Passenger email |
| phone | VARCHAR(20) | YES | NULL | Passenger phone |
| date_of_birth | DATE | YES | NULL | Passenger DOB |
| passport_number | VARCHAR(50) | YES | NULL | Passenger passport |
| created_at | TIMESTAMP | YES | NULL | Record creation time |
| updated_at | TIMESTAMP | YES | NULL | Record update time |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (booking_id) → bookings(id) ON DELETE CASCADE
- FOREIGN KEY (seat_id) → seats(id) ON DELETE RESTRICT
- INDEX (booking_id)

**Business Rules:**
- One passenger per seat
- Multiple passengers per booking

---

### 9. price_history
**Purpose:** Immutable log of price changes over time

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO | Primary key |
| flight_id | BIGINT UNSIGNED | NO | - | Foreign key to flights |
| fare_class_id | BIGINT UNSIGNED | NO | - | Foreign key to fare_classes |
| price | DECIMAL(10,2) | NO | - | Calculated price |
| factors | JSON | YES | NULL | All factors (JSON) |
| time_factor | DECIMAL(5,2) | YES | NULL | Time-based multiplier |
| inventory_factor | DECIMAL(5,2) | YES | NULL | Inventory multiplier |
| demand_factor | DECIMAL(5,2) | YES | NULL | Demand multiplier |
| recorded_at | TIMESTAMP | NO | - | When price recorded |
| created_at | TIMESTAMP | YES | NULL | Record creation time |
| updated_at | TIMESTAMP | YES | NULL | Record update time |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (flight_id) → flights(id) ON DELETE CASCADE
- FOREIGN KEY (fare_class_id) → fare_classes(id) ON DELETE RESTRICT
- INDEX (flight_id, fare_class_id, recorded_at)
- INDEX (recorded_at)

**Business Rules:**
- Never update or delete records (append-only)
- Recorded automatically when price changes
- Used for analytics and auditing

---

## Relationships

### One-to-Many
- `aircraft` → `flights`
- `flights` → `seats`
- `flights` → `bookings`
- `flights` → `price_history`
- `fare_classes` → `seats`
- `fare_classes` → `bookings`
- `fare_classes` → `price_history`
- `users` → `bookings`
- `bookings` → `passengers`

### One-to-One
- `fare_classes` → `fare_rules`
- `seats` → `passengers`

---

## Entity Relationship Diagram

```
┌──────────┐       ┌──────────┐       ┌──────────┐
│ aircraft │──────<│ flights  │>──────│  seats   │
└──────────┘       └──────────┘       └──────────┘
                         │                   │
                         │                   │
                         v                   v
                   ┌──────────┐       ┌──────────┐
                   │ bookings │>──────│passengers│
                   └──────────┘       └──────────┘
                         ^
                         │
                   ┌──────────┐
                   │  users   │
                   └──────────┘

┌─────────────┐       ┌────────────┐
│fare_classes │───────│ fare_rules │
└─────────────┘       └────────────┘
      │
      v
┌──────────────┐
│price_history │
└──────────────┘
```

---

## Concurrency Handling

### Seat Booking Race Condition
**Problem:** Two users try to book the last seat simultaneously.

**Solution:** Use database row-level locking:
```php
DB::transaction(function() {
    $seat = Seat::where('id', $seatId)
        ->lockForUpdate()
        ->first();
    
    if ($seat->status === 'available') {
        $seat->hold();
    }
});
```

---

## Data Integrity Rules

1. **Cascading Deletes:**
   - Delete booking → Delete passengers (orphan cleanup)
   - Delete flight → Delete seats (no orphan seats)

2. **Restrict Deletes:**
   - Can't delete aircraft if flights exist
   - Can't delete fare_class if seats/bookings exist

3. **Immutable Records:**
   - `price_history` - Never UPDATE or DELETE

4. **Atomic Operations:**
   - Booking creation + seat holds = single transaction
   - Booking confirmation + seat status change = single transaction

---

## Indexing Strategy

### Query Performance Optimizations

1. **Flight Search:**
   - Index on `(origin, destination, departure_time)`

2. **Seat Availability:**
   - Index on `(flight_id, fare_class_id, status)`

3. **Expiring Holds:**
   - Index on `hold_expires_at`

4. **Price History Queries:**
   - Index on `(flight_id, fare_class_id, recorded_at)`

---

## Storage Estimates

| Table | Rows (Estimate) | Size per Row | Total Size |
|-------|-----------------|--------------|------------|
| aircraft | 10 | 200 B | 2 KB |
| fare_classes | 3 | 150 B | 450 B |
| fare_rules | 3 | 300 B | 900 B |
| flights | 100 | 300 B | 30 KB |
| seats | 18,000 | 150 B | 2.7 MB |
| bookings | 5,000 | 400 B | 2 MB |
| passengers | 10,000 | 300 B | 3 MB |
| price_history | 50,000 | 250 B | 12.5 MB |
| users | 1,000 | 250 B | 250 KB |

**Total Estimated:** ~20 MB (without indexes)

---

## Backup & Maintenance

### Recommended Cleanup Jobs

1. **Archive Old Price History:**
   - Keep last 90 days
   - Archive older records to cold storage

2. **Clean Expired Bookings:**
   - Auto-expire held bookings after 15 minutes
   - Run every 1 minute

3. **Purge Cancelled Bookings:**
   - Keep cancelled bookings for 1 year
   - Archive after that

---

**Schema Version:** 1.0  
**Last Updated:** December 27, 2024
