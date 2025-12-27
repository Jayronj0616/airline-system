# Pricing Algorithm Documentation

## Overview
The airline pricing system uses dynamic pricing that adjusts ticket prices based on three key factors: time to departure, seat availability, and demand. This document explains how prices are calculated.

---

## Base Pricing Structure

Each flight has three base fare prices set by administrators:
- **Economy Class** (`base_price_economy`)
- **Business Class** (`base_price_business`)
- **First Class** (`base_price_first`)

These base prices are the foundation for all dynamic pricing calculations.

---

## Pricing Formula

```
Final Price = Base Fare × Time Factor × Inventory Factor × Demand Factor
```

### Example Calculation
```
Base Fare: ₱100 (Economy)
Time Factor: 1.5x (10 days before departure)
Inventory Factor: 1.4x (20% seats remaining)
Demand Factor: 1.2x (Medium demand)

Final Price = ₱100 × 1.5 × 1.4 × 1.2 = ₱252
```

---

## Factor Breakdown

### 1. Time Factor
Prices increase as departure approaches.

| Days Until Departure | Multiplier |
|---------------------|------------|
| 0-7 days            | 2.0x       |
| 8-14 days           | 1.5x       |
| 15-30 days          | 1.2x       |
| 30+ days            | 1.0x       |

**Implementation:** `PricingService::getTimeFactor()`

**Business Logic:**
- Last-minute bookings pay premium prices
- Early birds get the best rates
- Encourages advance booking

---

### 2. Inventory Factor
Prices increase as seats sell out.

| Seat Availability | Multiplier |
|------------------|------------|
| < 10% available  | 1.8x       |
| 10-30% available | 1.4x       |
| 30-60% available | 1.1x       |
| 60%+ available   | 1.0x       |

**Implementation:** `PricingService::getInventoryFactor()`

**Business Logic:**
- Scarcity drives higher prices
- Full flights command premium pricing
- Applied per fare class independently

---

### 3. Demand Factor
Prices adjust based on simulated demand score (0-100).

| Demand Score | Multiplier |
|-------------|------------|
| 80-100      | 1.5x       |
| 40-79       | 1.2x       |
| 0-39        | 1.0x       |

**Implementation:** `PricingService::getDemandFactor()`

**Business Logic:**
- High demand routes cost more
- Popular times (holidays, events) have higher scores
- Demand simulation happens in Phase 5

---

## Price History Tracking

Every price calculation is logged to the `price_history` table with:
- `flight_id` - Which flight
- `fare_class_id` - Which cabin class
- `price` - Calculated final price
- `factors_used` - JSON object containing all multipliers used
- `created_at` - Timestamp of calculation

**This is append-only data** - never update or delete price history.

### Sample Price History Record
```json
{
  "flight_id": 15,
  "fare_class_id": 1,
  "price": 252.00,
  "factors_used": {
    "time_factor": 1.5,
    "inventory_factor": 1.4,
    "demand_factor": 1.2,
    "base_fare": 100.00
  },
  "created_at": "2024-12-27 10:30:00"
}
```

---

## Price Updates

### Manual Updates
Administrators can:
1. Update base fares via `/admin/pricing/{flight}/edit`
2. Trigger immediate recalculation
3. View price history trends

### Automated Updates
```bash
# Update all future flights
php artisan pricing:update

# Update specific flight
php artisan pricing:update --flight=15

# Verbose output
php artisan pricing:update -v
```

**Recommended Schedule:**
- Run every hour during peak booking times
- Run every 4-6 hours during off-peak
- Run immediately after booking events

---

## Price Locking

When a customer begins the booking process:
1. Current price is calculated
2. Price is locked for 15 minutes
3. Locked price stored in `bookings.locked_price`
4. Customer has 15 minutes to complete payment

**This prevents price changes mid-booking.**

---

## Edge Cases

### 1. Flight Has Departed
- **Behavior:** `calculateCurrentPrice()` returns `null`
- **UI:** Flight hidden from search results

### 2. Fare Class Sold Out
- **Behavior:** `calculateCurrentPrice()` returns `null`
- **UI:** Shows "SOLD OUT" badge

### 3. Base Fare Not Set
- **Behavior:** `calculateCurrentPrice()` returns `null`
- **Fix:** Admin must set base fares

### 4. Zero Demand Score
- **Behavior:** Demand factor defaults to 1.0x (no increase)
- **Note:** Negative demand not currently supported

---

## Price Trends

The system tracks price movement over time:
- **↑** Price increased (compared to 1 hour ago)
- **↓** Price decreased
- **→** Price stable

**Implementation:** `PricingService::getPriceTrend()`

---

## Testing Price Calculations

### Unit Test Example
```php
// Test time factor for flight departing in 5 days
$flight = Flight::factory()->create([
    'departure_time' => Carbon::now()->addDays(5)
]);

$timeFactor = $pricingService->getTimeFactor($flight);
$this->assertEquals(2.0, $timeFactor);
```

### Manual Testing Checklist
- [ ] Flight departing in 1 hour shows 2.0x time factor
- [ ] 100% sold out fare class returns null price
- [ ] Price history appends, never overwrites
- [ ] Admin can update base fares
- [ ] Recalculation command works
- [ ] Price locks during booking hold

---

## Performance Considerations

### Caching Strategy (Future Enhancement)
Currently, prices are calculated on-demand. For high traffic:
1. Cache calculated prices for 5 minutes
2. Invalidate cache on:
   - Booking completion
   - Manual recalculation
   - Base fare update

### Database Optimization
- Index `price_history.flight_id` and `price_history.fare_class_id`
- Index `price_history.created_at` for trend queries
- Partition `price_history` by month for older data

---

## Next Phase

Once pricing engine is complete and tested, proceed to:
**Phase 3 - Inventory Management**
- Seat status management
- Booking holds
- Seat locking mechanisms
