# System Constraints

## Technical Constraints

### Platform & Infrastructure
- **Laravel Version:** 10.x (PHP 8.1+)
- **Database:** MySQL 8.0+ (no PostgreSQL features)
- **Frontend:** Blade templates only (no Vue/React for MVP)
- **Hosting:** Local development (Laragon/XAMPP)
- **No CDN:** Assets served locally

### Performance Constraints
- **Target Users:** 100 concurrent users (demo scale)
- **Database Size:** <1GB for MVP
- **Response Time:** <2 seconds for flight search
- **Background Jobs:** Process within 5 minutes
- **Price Update Frequency:** Maximum every 5 minutes (cached)

### Data Constraints
- **Flights:** Max 100 active flights at once
- **Bookings:** No limit (but optimized queries required)
- **Price History:** Keep last 90 days only
- **Demand Logs:** Keep last 30 days only
- **Session Timeout:** 15 minutes (seat hold duration)

### Security Constraints
- **Authentication:** Required for booking (Breeze)
- **Authorization:** Role-based (Passenger vs Admin)
- **Payment:** Mock only (no PCI compliance needed)
- **Data Encryption:** Laravel's default encryption
- **No Rate Limiting:** For demo purposes

---

## Business Constraints

### Operational Rules
- **Single Airline:** No multi-tenant architecture
- **Mock Payments:** No real money transactions
- **Simulated Demand:** No real user traffic expected
- **No Refunds:** System tracks refund eligibility but doesn't process real refunds
- **English Only:** No internationalization (i18n)

### Booking Rules
- **Seat Hold Duration:** Fixed at 15 minutes (not configurable by users)
- **Max Passengers Per Booking:** 9 (industry standard)
- **Min Booking Window:** 2 hours before departure
- **Cancellation:** Allowed up to 24 hours before departure (varies by fare class)

### Pricing Rules
- **Base Fare Range:** $50 - $5000 per seat
- **Max Price Multiplier:** 5x base fare (to prevent absurd prices)
- **Price Update Frequency:** Real-time on search, cached for 5 minutes
- **Overbooking Limit:** Maximum 15% over physical capacity

### Inventory Rules
- **Physical Seats:** Determined by aircraft type (150-400 seats)
- **Fare Classes:** Fixed at 3 (Economy, Business, First)
- **Seat Allocation:** Static percentages (70% Economy, 20% Business, 10% First)
- **No Dynamic Reallocation:** Can't convert Business seats to Economy mid-flight

---

## Time Constraints

### Development Timeline
- **MVP Deadline:** 4 weeks (part-time work)
- **Phase Duration:** 2-5 days per phase
- **No Scope Creep:** Stick to must-have features only

### System Timing
- **Seat Hold Expiration:** 15 minutes (non-negotiable)
- **Demand Simulation:** Runs every 15 minutes
- **Hold Expiration Check:** Runs every 1 minute
- **Demand Decay:** Runs every 1 hour
- **Price Cache:** 5 minutes TTL

---

## Data Integrity Constraints

### Must Maintain
- **Atomic Bookings:** Either fully created or rolled back (no partial bookings)
- **Price History Immutability:** Never update/delete price records, only append
- **Seat Availability Accuracy:** Never allow negative seats
- **Booking Reference Uniqueness:** No duplicate booking references

### Can Tolerate
- **Eventual Consistency:** Demand scores may lag by 15 minutes
- **Cache Staleness:** Prices cached for up to 5 minutes
- **Log Delays:** Background job logs may be 1-2 minutes behind

---

## Integration Constraints

### No External APIs
- ❌ No real flight data (FlightAware, Amadeus)
- ❌ No real payment gateways (Stripe, PayPal) - mock only
- ❌ No email services (Mailgun, SendGrid) - optional local only
- ❌ No SMS notifications
- ❌ No third-party analytics (Google Analytics)

### Only Allowed
- ✅ Laravel's built-in features
- ✅ Composer packages (open-source, MIT/Apache)
- ✅ NPM packages (for frontend)
- ✅ Local SMTP (Mailtrap for testing)

---

## Scalability Constraints

### Not Designed For
- ❌ 10,000+ concurrent users
- ❌ Multi-region deployment
- ❌ Load balancing
- ❌ Microservices architecture
- ❌ Message queues (RabbitMQ, Kafka)

### Designed For
- ✅ Single-server deployment
- ✅ 100 concurrent users
- ✅ Monolithic architecture
- ✅ Laravel's queue system (database driver)

---

## Browser Compatibility

### Supported
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

### Not Supported
- ❌ Internet Explorer
- ❌ Mobile browsers (responsive but not optimized)

---

## Legal & Compliance Constraints

### No Real-World Liability
- This is a **demonstration system**
- Not for actual airline operations
- No real financial transactions
- No real passenger data (use fake data only)
- No GDPR/CCPA compliance required (but good to implement)

### Disclaimers Required
- "This is a simulation for educational/portfolio purposes"
- "No real flights or payments"
- "Data may be reset at any time"

---

## Testing Constraints

### Required
- ✅ Unit tests for pricing logic
- ✅ Feature tests for booking flow
- ✅ Manual testing of concurrency (10 simultaneous requests)

### Not Required (Nice to Have)
- ❌ E2E tests (Selenium, Cypress)
- ❌ Load testing (JMeter, k6)
- ❌ Penetration testing
- ❌ Automated accessibility testing

---

## Documentation Constraints

### Must Document
- ✅ Setup instructions (README)
- ✅ Database schema (ER diagram)
- ✅ Pricing algorithm (formula + examples)
- ✅ API endpoints (if any)
- ✅ Background jobs (what they do)

### Nice to Have
- ⚠️ Architecture diagram (highly recommended)
- ⚠️ User manual
- ⚠️ Admin guide

---

## Known Limitations

### Accepted Trade-offs
1. **No real-time updates** - User must refresh to see price changes (acceptable for MVP)
2. **No WebSockets** - Seat hold countdown timer is client-side only (not real-time synced)
3. **Simplified concurrency** - Database locks work but not Redis-level performance
4. **Mock payments** - No refund processing, just status changes
5. **Static aircraft types** - Can't add new aircraft without seeding

### Future Improvements (Post-MVP)
- Upgrade to API + SPA for better UX
- Add Redis for session management
- Implement real payment gateway
- Add email notifications
- Support multiple airlines

---

**These constraints keep the project focused and achievable within 4 weeks.**
