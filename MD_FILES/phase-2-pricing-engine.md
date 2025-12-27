# Phase 2 - Pricing Engine (CORE FEATURE)

**Goal:** Build the dynamic pricing algorithm that adjusts prices based on demand, inventory, and time.

---

## Tasks

### 1. Base Fare Setup
- [x] Define base fare structure per fare class
- [x] Store base fares in `fare_classes` table or separate config
- [x] Create admin interface to set base fares

### 2. Pricing Factor Implementation
- [x] **Time Factor** - Price multiplier based on days before departure
  - [x] 0-7 days: 2.0x
  - [x] 8-14 days: 1.5x
  - [x] 15-30 days: 1.2x
  - [x] 30+ days: 1.0x
- [x] **Inventory Factor** - Price multiplier based on seat availability %
  - [x] <10% available: 1.8x
  - [x] 10-30% available: 1.4x
  - [x] 30-60% available: 1.1x
  - [x] 60%+ available: 1.0x
- [x] **Demand Factor** - Price multiplier based on simulated demand score
  - [x] High demand (80-100): 1.5x
  - [x] Medium demand (40-79): 1.2x
  - [x] Low demand (0-39): 1.0x

### 3. Pricing Service Creation
- [x] Create `PricingService` class
- [x] Method: `calculateCurrentPrice(Flight $flight, FareClass $fareClass)`
- [x] Method: `getTimeFactor(Flight $flight)`
- [x] Method: `getInventoryFactor(Flight $flight, FareClass $fareClass)`
- [x] Method: `getDemandFactor(Flight $flight)`
- [x] Method: `recordPriceHistory(Flight $flight, FareClass $fareClass, $price)`

### 4. Price History Tracking
- [x] Log every price change to `price_history` table
- [x] Track: flight_id, fare_class_id, price, factors_used (JSON), created_at
- [x] Never overwrite - append only (time-series data)

### 5. Price Display
- [x] Create flight search page with Blade
- [x] Show current price per fare class
- [x] Show price trend indicator (↑ ↓ →)
- [x] Show "Price last updated: X minutes ago"

### 6. Price Locking on Booking
- [x] Lock price when user starts booking (seat hold)
- [x] Store locked price in `seat_holds` or `bookings`
- [x] Price freeze duration: 15 minutes

### 7. Testing
- [x] Unit test `PricingService` methods
- [x] Test edge case: flight departs in 1 hour (should max out multipliers)
- [x] Test edge case: flight 100% sold out (should return null or max price)
- [x] Test price history is never overwritten

---

## Deliverables
- [x] `PricingService` fully implemented
- [x] Price calculation formula documented in `PRICING_ALGORITHM.md`
- [x] Price history tracking works
- [x] Flight search page displays dynamic prices
- [x] Unit tests pass

---

## Formula Example
```
Base Fare: $100 (Economy)
Time Factor: 1.5x (10 days before departure)
Inventory Factor: 1.4x (20% seats left)
Demand Factor: 1.2x (Medium demand)

Final Price = $100 × 1.5 × 1.4 × 1.2 = $252
```

---

## Edge Cases to Handle
- Flight departed (return error or hide from search)
- Flight 100% sold out (what price to show? Should it even appear?)
- Negative demand (if you later simulate cancellations)

---

## Next Phase
Once pricing engine works and is tested, move to `phase-3-inventory-management.md`
