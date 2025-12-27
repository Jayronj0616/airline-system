# Feature List

## Must-Have Features (MVP - Phase 1-5)

### Passenger Features
- [ ] **Flight Search**
  - Search by origin, destination, date
  - Filter by fare class
  - View real-time pricing
  - See available seats per fare class

- [ ] **Booking Flow**
  - Select flight and fare class
  - Enter passenger details
  - Hold seats (15-minute timer)
  - Mock payment process
  - Receive booking confirmation

- [ ] **Booking Management**
  - View booking details
  - Cancel booking (based on fare rules)
  - Request refund (if applicable)

### Admin Features
- [ ] **Revenue Dashboard**
  - Total revenue (current month)
  - Total bookings
  - Average load factor
  - Average ticket price
  - Revenue by fare class (pie chart)
  - Revenue over time (line chart)
  - Load factor by flight (bar chart)

- [ ] **Flight Management**
  - View all flights
  - See booking status per flight
  - Monitor seat availability

- [ ] **Fare Rules Management**
  - Configure refund policies
  - Set change fees
  - Define baggage allowances
  - Edit rules per fare class

### System Features
- [ ] **Dynamic Pricing Engine**
  - Base fare per fare class
  - Time-based multiplier (days to departure)
  - Inventory-based multiplier (% seats left)
  - Demand-based multiplier (simulated demand score)
  - Price history tracking (never overwrite)

- [ ] **Inventory Management**
  - Real-time seat availability
  - Seat hold mechanism (15-min expiration)
  - Prevent overselling
  - Concurrency handling (database locks)

- [ ] **Demand Simulation**
  - Background job: Simulate searches
  - Background job: Simulate bookings
  - Update demand score per flight
  - Demand affects pricing in real-time

- [ ] **Background Jobs**
  - Expire seat holds (every 1 minute)
  - Simulate demand (every 15 minutes)
  - Decay demand scores (every 1 hour)

---

## Nice-to-Have Features (Phase 6-10)

### Advanced Pricing
- [ ] **Overbooking Logic**
  - Allow bookings beyond physical capacity
  - Configurable overbooking percentage (5-15%)
  - Safety rules (disable near departure)
  - Track no-show probability

- [ ] **Surge Pricing**
  - Weekend/holiday multipliers
  - Popular route premiums
  - Last-minute booking fees

### User Experience
- [ ] **Price Alerts**
  - User sets price watch
  - Email when price drops

- [ ] **Booking History**
  - Past bookings
  - Frequent flyer tracking

- [ ] **Seat Selection**
  - Visual seat map
  - Premium seat fees

### Admin Features
- [ ] **Advanced Analytics**
  - Yield management (revenue per seat)
  - Booking conversion rate
  - Price elasticity analysis
  - Demand forecasting

- [ ] **Flight Operations**
  - Create/edit flights
  - Manage aircraft types
  - Set flight schedules

- [ ] **User Management**
  - View all users
  - View booking history per user
  - Ban/suspend users

### System Enhancements
- [ ] **Email Notifications**
  - Booking confirmation
  - Cancellation confirmation
  - Flight reminder (24 hours before)

- [ ] **API Endpoints**
  - RESTful API for mobile app
  - API documentation

- [ ] **Payment Integration**
  - Stripe/PayPal integration
  - Real payment processing
  - Refund handling

---

## Explicitly Out of Scope

### Not Building (Even in Future)
- ❌ Real flight data integration
- ❌ Multi-airline support
- ❌ Check-in system
- ❌ Baggage tracking
- ❌ In-flight services
- ❌ Loyalty points/miles
- ❌ Travel insurance
- ❌ Hotel/car rental booking
- ❌ Group bookings
- ❌ Travel agency/agent portal

---

## Feature Priority Matrix

| Feature | Priority | Complexity | MVP? |
|---------|----------|------------|------|
| Flight Search | HIGH | Low | ✅ |
| Dynamic Pricing | HIGH | High | ✅ |
| Seat Hold | HIGH | Medium | ✅ |
| Booking Confirmation | HIGH | Low | ✅ |
| Admin Dashboard | HIGH | Medium | ✅ |
| Demand Simulation | MEDIUM | Medium | ✅ |
| Overbooking | MEDIUM | High | ⚠️ Phase 6 |
| Fare Rules Engine | MEDIUM | Medium | ⚠️ Phase 7 |
| Email Notifications | LOW | Low | ❌ Post-MVP |
| Payment Integration | LOW | Medium | ❌ Post-MVP |
| Seat Selection | LOW | High | ❌ Post-MVP |

---

## Decision Log

### Why Mock Payments?
Real payment integration adds complexity without demonstrating core skills (pricing algorithms, concurrency, domain modeling). Focus on the hard problems first.

### Why Single Airline?
Multi-tenancy adds database complexity. Prove you can build one airline system well before scaling to multiple.

### Why Blade Templates?
Faster development for MVP. Can refactor to API + SPA later if needed. Demonstrates full-stack capability.

### Why Demand Simulation?
Real user traffic won't exist in a demo. Simulation makes the system feel "alive" and demonstrates background job architecture.

---

**This feature list guides what we build and what we skip.**
