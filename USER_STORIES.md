# User Stories

## Passenger (Customer) Stories

### Flight Search
**As a passenger,**  
I want to search for flights by origin, destination, and date  
So that I can find available flights for my trip.

**Acceptance Criteria:**
- Search form accepts: origin, destination, departure date
- Results show all matching flights
- Each result displays: flight number, departure/arrival times, duration, available seats per fare class, current price
- If no flights found, show friendly message

---

**As a passenger,**  
I want to see prices for different fare classes (Economy, Business, First)  
So that I can choose the best option for my budget.

**Acceptance Criteria:**
- Each fare class shows: price, baggage allowance, refund policy, change fee
- Prices are clearly labeled and formatted (e.g., "$250")
- Fare rules are visible before booking

---

**As a passenger,**  
I want to see how many seats are left in each fare class  
So that I can decide quickly if I should book now.

**Acceptance Criteria:**
- Display "X seats left" if <10 seats
- Display "Limited availability" if <5 seats
- Display "Available" if >10 seats
- Don't show exact count if >10 (avoid information advantage)

---

### Booking Process

**As a passenger,**  
I want to hold my selected seats for 15 minutes while I complete booking  
So that I don't lose my seat to another customer.

**Acceptance Criteria:**
- After selecting flight and fare class, seats are held
- Countdown timer shows time remaining (15:00 → 0:00)
- If timer expires, show message and redirect to search
- Held seats not shown as available to other users

---

**As a passenger,**  
I want the price to stay the same during my 15-minute hold  
So that I'm not surprised by price increases at checkout.

**Acceptance Criteria:**
- Price locked when hold is created
- Price displayed on all checkout pages matches locked price
- If hold expires and user re-books, new price applies

---

**As a passenger,**  
I want to enter passenger details (name, email, phone) before payment  
So that the airline has my contact information.

**Acceptance Criteria:**
- Form validates: required fields, email format, phone format
- Support multiple passengers (up to 9)
- Save passenger details to booking record

---

**As a passenger,**  
I want to complete payment (mock) and receive booking confirmation  
So that I have proof of my booking.

**Acceptance Criteria:**
- Mock payment form (card number, expiry, CVV) - always succeeds
- On success: Show booking reference, flight details, passenger names
- Booking status changes from HELD → CONFIRMED
- Seats released from hold, marked as confirmed

---

### Booking Management

**As a passenger,**  
I want to view my booking details using my booking reference  
So that I can check my flight information.

**Acceptance Criteria:**
- Enter booking reference + email
- Show: flight details, passenger names, booking status, fare rules
- If booking not found, show error

---

**As a passenger,**  
I want to cancel my booking if allowed by fare rules  
So that I can get a refund or avoid no-show penalties.

**Acceptance Criteria:**
- Show cancellation policy before confirming cancel
- If refundable: Process cancellation, show refund amount (mock)
- If non-refundable: Show "Cancellation not allowed" message
- On cancel: Booking status → CANCELLED, seats released to inventory

---

**As a passenger,**  
I want to see if my booking has expired due to payment timeout  
So that I understand why my seats were released.

**Acceptance Criteria:**
- If hold expires, booking status → EXPIRED
- Show message: "Your booking expired after 15 minutes. Please search again."
- Seats automatically released back to inventory

---

## Admin (Revenue Manager) Stories

### Dashboard & Analytics

**As an admin,**  
I want to see total revenue for the current month  
So that I can track business performance.

**Acceptance Criteria:**
- Dashboard shows: Total revenue (this month)
- Compare to previous month (% change)
- Filter by date range

---

**As an admin,**  
I want to see average load factor across all flights  
So that I can identify underperforming routes.

**Acceptance Criteria:**
- Display load factor % per flight
- Highlight flights <70% (red), 70-85% (yellow), >85% (green)
- Sort by load factor (ascending/descending)

---

**As an admin,**  
I want to see revenue breakdown by fare class  
So that I can optimize pricing strategies.

**Acceptance Criteria:**
- Pie chart: Economy vs Business vs First revenue
- Show percentage and absolute value
- Filter by date range

---

**As an admin,**  
I want to see price trends over time for a specific flight  
So that I can understand how demand affects pricing.

**Acceptance Criteria:**
- Select a flight
- Line chart: Price over time (last 30 days)
- Mark booking events on chart (dots)
- Show demand score on secondary axis

---

### Flight Management

**As an admin,**  
I want to view all flights and their booking status  
So that I can monitor operations.

**Acceptance Criteria:**
- Table: Flight number, route, departure time, seats sold, seats available, load factor, revenue
- Sortable by any column
- Filter by date or route

---

**As an admin,**  
I want to see demand trends for each flight  
So that I can predict which flights will sell out.

**Acceptance Criteria:**
- Show demand score (0-100) per flight
- Indicate trend: ↑ increasing, → stable, ↓ decreasing
- Highlight flights with high demand (>80) for price increase consideration

---

### Fare Rules Management

**As an admin,**  
I want to configure refund policies per fare class  
So that I can offer different service levels.

**Acceptance Criteria:**
- Edit page for each fare class
- Set: refundable (yes/no), refund fee (%), cancellation fee ($)
- Preview: "Economy is non-refundable"

---

**As an admin,**  
I want to set baggage allowances per fare class  
So that passengers know what's included.

**Acceptance Criteria:**
- Set: Number of checked bags, weight limit per bag
- Display on booking flow and confirmation

---

### Overbooking Management

**As an admin,**  
I want to enable/disable overbooking per flight  
So that I can maximize revenue while managing risk.

**Acceptance Criteria:**
- Toggle overbooking on/off globally or per flight
- Set overbooking percentage (5%, 10%, 15%)
- View flights at risk of denied boarding

---

**As an admin,**  
I want to see which flights are overbooked  
So that I can prepare for potential denied boarding.

**Acceptance Criteria:**
- Alert: "Flight X is 105% booked (189/180 seats)"
- Show expected no-show count
- Calculate risk level: Low, Medium, High

---

## System (Automated) Stories

### Background Jobs

**As the system,**  
I want to automatically expire seat holds after 15 minutes  
So that inventory is released for other customers.

**Acceptance Criteria:**
- Job runs every 1 minute
- Find all bookings where status=HELD and expires_at < now()
- Change status to EXPIRED
- Release seats back to inventory
- Log expiration event

---

**As the system,**  
I want to simulate demand by generating fake searches and bookings  
So that the pricing engine has realistic data to work with.

**Acceptance Criteria:**
- Job runs every 15 minutes
- Randomly select 20-50% of flights
- Simulate 5-20 searches per flight
- Simulate 1-5 bookings per flight
- Update demand_score based on activity

---

**As the system,**  
I want to decay demand scores over time if no activity  
So that demand reflects current interest, not historical.

**Acceptance Criteria:**
- Job runs every 1 hour
- Reduce demand_score by 1-3 points per flight
- Minimum demand_score = 0
- Log decay event

---

**As the system,**  
I want to recalculate prices when demand or inventory changes  
So that prices stay competitive and maximize revenue.

**Acceptance Criteria:**
- Triggered when: demand_score changes, seats sold, time to departure changes
- Calculate: Base fare × Time factor × Inventory factor × Demand factor
- Store new price in price_history (never overwrite)
- Cache price for 5 minutes

---

**As the system,**  
I want to log all price changes with reasons  
So that admins can audit pricing decisions.

**Acceptance Criteria:**
- Each price change logged to price_history table
- Store: flight_id, fare_class_id, old_price, new_price, factors_used (JSON), timestamp
- Never delete price history (audit trail)

---

**As the system,**  
I want to prevent overselling by checking inventory before confirming bookings  
So that we never have more confirmed bookings than seats.

**Acceptance Criteria:**
- Use database locks (lockForUpdate) during booking
- Check: available_seats - held_seats >= requested_seats
- If not enough: Return error, don't create booking
- If overbooking enabled: Check against virtual capacity instead

---

## Edge Case Stories

**As a passenger,**  
When I try to book the last seat and someone else books it first,  
I should see an error message saying seats are no longer available.

**Acceptance Criteria:**
- Database lock prevents race condition
- Second user gets clear error message
- No partial bookings created

---

**As a passenger,**  
When my seat hold expires while I'm on the payment page,  
I should be notified and redirected to search again.

**Acceptance Criteria:**
- Backend checks hold validity before confirming
- Show error: "Your seat hold has expired. Please search again."
- Don't charge (even in mock payment)

---

**As an admin,**  
When a flight is 100% sold out,  
I should not see it in search results (unless overbooking is enabled).

**Acceptance Criteria:**
- Hide flights with 0 available seats
- If overbooking enabled and virtual capacity available, still show

---

**As the system,**  
When a booking is cancelled,  
I should release seats back to inventory immediately.

**Acceptance Criteria:**
- Update available_seats count
- Trigger price recalculation (supply increased, price may drop)
- Log seat release event

---

**These user stories guide what we build in each phase.**
