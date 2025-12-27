# Phase 2 - Pricing Engine (COMPLETE)

## What Was Built

### Core Service
- **PricingService** (`app/Services/PricingService.php`)
  - Dynamic price calculation with 3 factors
  - Price history tracking
  - Batch price updates for all flights
  - Price trend analysis

### Controllers
- **FlightController** (`app/Http/Controllers/FlightController.php`)
  - Public flight search
  - Flight detail view
  - Real-time price display

- **Admin/PricingController** (`app/Http/Controllers/Admin/PricingController.php`)
  - Base fare management
  - Manual price recalculation
  - Bulk price updates

### Views
- **flights/search.blade.php** - Public flight search with filters and pricing
- **flights/show.blade.php** - Flight details with fare class breakdown
- **admin/pricing/index.blade.php** - Admin pricing overview
- **admin/pricing/edit.blade.php** - Edit base fares per flight

### Artisan Command
- **pricing:update** - Batch update all flight prices
  - `php artisan pricing:update` - Update all future flights
  - `php artisan pricing:update --flight=15` - Update specific flight
  - `php artisan pricing:update -v` - Verbose output

### Tests
- **PricingServiceTest.php** - 14 comprehensive unit tests
  - Time factor calculations
  - Inventory factor calculations
  - Demand factor calculations
  - Final price calculation
  - Edge cases (departed flights, sold out)
  - Price history tracking

### Documentation
- **PRICING_ALGORITHM.md** - Complete pricing formula documentation

### Database
- Price history tracking already set up (from Phase 1)
- Base fares stored in flights table

## How to Use

### For Admins
1. Go to `/admin/pricing`
2. Edit base fares for any flight
3. System auto-recalculates prices
4. Use "Recalculate All" to update all flights at once

### For Passengers
1. Go to `/flights/search`
2. Filter by origin, destination, date
3. View real-time prices for all fare classes
4. See price trends (↑ ↓ →)
5. Click flight to see detailed breakdown

### Automated Updates
Schedule this command to run every hour:
```bash
php artisan pricing:update
```

## Pricing Formula

```
Final Price = Base Fare × Time Factor × Inventory Factor × Demand Factor
```

### Time Factor
- 0-7 days: 2.0x
- 8-14 days: 1.5x
- 15-30 days: 1.2x
- 30+ days: 1.0x

### Inventory Factor
- <10% seats: 1.8x
- 10-30% seats: 1.4x
- 30-60% seats: 1.1x
- 60%+ seats: 1.0x

### Demand Factor
- 80-100 score: 1.5x
- 40-79 score: 1.2x
- 0-39 score: 1.0x

## Testing

Run the tests:
```bash
php artisan test --filter=PricingServiceTest
```

All 14 tests should pass.

## Next Steps

Move to **Phase 3 - Inventory Management**:
- Seat status management
- Booking holds
- Seat locking
- Hold expiration handling
