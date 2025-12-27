# Phase 8 - Admin Revenue Dashboard

**Goal:** Show business value through analytics and reporting.

---

## Key Metrics

### 1. Load Factor
**Formula:** (Confirmed Bookings / Total Seats) × 100%  
**Target:** >85% (industry standard)

### 2. Revenue Per Flight
**Formula:** Sum of all confirmed booking prices for a flight  
**Goal:** Maximize through dynamic pricing

### 3. Yield (Revenue Per Seat)
**Formula:** Total Revenue / Total Seats Sold  
**Purpose:** Measure pricing effectiveness

### 4. Average Ticket Price
**Formula:** Total Revenue / Total Bookings  
**Track:** Over time to see pricing trends

### 5. Booking Conversion Rate
**Formula:** (Confirmed Bookings / Total Searches) × 100%  
**Goal:** >5-10% conversion rate

---

## Tasks

### 1. Dashboard Layout
- [x] Create route: `/admin/dashboard`
- [x] Middleware: Admin role only
- [x] Layout: 4-column grid with metric cards
- [x] Use Chart.js or ApexCharts for visualizations

### 2. Metric Cards (Top Row)
- [x] **Total Revenue** (this month)
- [x] **Total Bookings** (this month)
- [x] **Average Load Factor** (all flights)
- [x] **Average Ticket Price** (this month)

### 3. Charts

#### Chart 1: Revenue Over Time (Line Chart)
- [x] X-axis: Date (last 30 days)
- [x] Y-axis: Revenue ($)
- [x] Show daily revenue trend

#### Chart 2: Bookings by Fare Class (Pie Chart)
- [x] Economy: X%
- [x] Business: Y%
- [x] First: Z%

#### Chart 3: Load Factor by Flight (Bar Chart)
- [x] X-axis: Flight number
- [x] Y-axis: Load factor (%)
- [x] Color: Green >85%, Yellow 70-85%, Red <70%

#### Chart 4: Price vs Demand Correlation (Scatter Plot)
- [x] X-axis: Demand score
- [x] Y-axis: Average price
- [x] Show positive correlation

### 4. Flight Performance Table
- [x] Columns: Flight, Route, Load Factor, Revenue, Avg Ticket Price, Seats Sold
- [x] Sortable by any column
- [x] Filterable by date range
- [x] Export to CSV

### 5. Demand Trends
- [x] Show top 5 high-demand flights
- [x] Show top 5 low-demand flights
- [x] Suggest price adjustments

### 6. Price History Visualization
- [x] Select a flight
- [x] Show price changes over time (line chart)
- [x] Mark booking events on chart

### 7. Filters
- [x] Date range picker
- [x] Flight filter (dropdown)
- [x] Fare class filter

---

## Deliverables
- [x] Admin dashboard with 4+ charts (5 charts total: Revenue Over Time, Bookings by Fare Class, Load Factor by Flight, Price vs Demand, Price History)
- [x] Real-time metrics (cached for 5 minutes using Laravel Cache)
- [x] Exportable reports (CSV export available via export-csv route)
- [ ] `ANALYTICS.md` - Document metric definitions (TODO: Create this file)

---

## Sample Queries

### Total Revenue (This Month)
```php
DB::table('bookings')
    ->where('status', 'CONFIRMED')
    ->whereMonth('confirmed_at', now()->month)
    ->sum('final_price');
```

### Load Factor Per Flight
```php
Flight::with('bookings')->get()->map(function ($flight) {
    $confirmedSeats = $flight->bookings->where('status', 'CONFIRMED')->sum('seats_count');
    $totalSeats = $flight->aircraft->total_seats;
    return [
        'flight' => $flight->flight_number,
        'load_factor' => ($confirmedSeats / $totalSeats) * 100
    ];
});
```

### Average Ticket Price
```php
DB::table('bookings')
    ->where('status', 'CONFIRMED')
    ->avg('final_price');
```

---

## Design Considerations
- Use Tailwind CSS for styling (or Bootstrap if you prefer)
- Make it responsive (mobile-friendly)
- Add export buttons (CSV, PDF)
- Use caching (cache metrics for 5 minutes to reduce DB load)

---

## Optional Features
- Email reports (weekly/monthly)
- Alerts: "Flight X has <50% load factor, consider price drop"
- Predictive analytics: "Flight Y will likely sell out, increase price"

---

## Testing Scenarios
1. Generate sample bookings → Metrics update correctly
2. Filter by date range → Charts reflect filtered data
3. Export to CSV → File downloads with correct data
4. Cache invalidation → New booking updates metrics within 5 minutes

---

## Next Phase
Once dashboard is complete, move to `phase-9-failure-handling.md`
