# Phase 5 - Demand Simulation Engine

**Goal:** Make pricing feel "alive" by simulating user activity and demand.

---

## Tasks

### 1. Demand Score System ✅
- [x] Add `demand_score` column to `flights` table (0-100 scale)
- [x] Default demand score: 50 (medium)
- [x] Update demand score based on simulated activity

### 2. Demand Factors ✅
- [x] **Search activity** - Each search increases demand by 0.5-2 points
- [x] **Booking activity** - Each booking increases demand by 3-5 points
- [x] **Time decay** - Demand decreases by 1-3 points per hour if no activity
- [x] **Departure proximity** - Demand spikes 7 days before departure

### 3. Demand Simulation Job ✅
- [x] Command: `php artisan demand:simulate`
- [x] Run every 15 minutes via scheduler
- [x] Pick random flights (20-50% of total)
- [x] Simulate 5-20 searches per selected flight
- [x] Simulate 1-5 bookings per selected flight
- [x] Update `demand_score` based on activity

### 4. Demand → Price Impact ✅
- [x] Modify `PricingService::getDemandFactor()` to use `flights.demand_score`
- [x] High demand (80-100): 1.5x multiplier
- [x] Medium demand (40-79): 1.2x multiplier
- [x] Low demand (0-39): 1.0x multiplier

**Implementation:** `PricingService::getDemandFactor()` already implemented and integrated into `calculateCurrentPrice()`. Demand score directly affects final price calculation. Each price calculation records demand factor in price_history for tracking. See `PHASE_5_TASK_4_SUMMARY.md` for full details.

### 5. Demand Visualization (Admin Dashboard)
- [ ] Create admin page: `/admin/demand`
- [ ] Show chart: Demand score over time per flight
- [ ] Show chart: Price changes correlated with demand
- [ ] Use Chart.js or similar

### 6. Demand Decay Logic ✅
- [x] Command: `php artisan demand:decay`
- [x] Run every hour
- [x] Reduce `demand_score` by 1-3 points if no recent activity

### 7. Search Activity Tracking ✅
- [x] Add `flight_searches` table (optional)
- [x] Fields: flight_id, user_id (nullable), searched_at
- [x] Log every flight search

### 8. Realistic Demand Patterns ✅
- [x] Popular routes have higher base demand
- [x] Weekend flights have higher demand
- [x] Holiday periods have surge demand
- [x] Red-eye flights have lower demand

---

## Deliverables
- [x] Demand score methods in Flight model
- [x] Search activity tracking in controllers
- [x] Booking activity tracking in services
- [x] Demand decay command scheduled hourly
- [x] Departure proximity boost scheduled daily
- [x] Demand simulation job runs every 15 minutes
- [x] Demand score affects pricing in real-time
- [ ] Admin dashboard shows demand trends
- [ ] `DEMAND_ALGORITHM.md` - Document how demand is calculated

---

## Next Phase
Once demand simulation is working, move to `phase-6-overbooking-logic.md`
