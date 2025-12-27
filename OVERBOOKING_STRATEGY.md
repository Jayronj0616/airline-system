# Overbooking Strategy Documentation

## Overview
This document outlines the overbooking strategy implementation for the Airline Reservation System. Overbooking is an industry-standard practice where airlines intentionally sell more seats than physically available to compensate for expected passenger no-shows, maximizing revenue and seat utilization.

---

## What is Overbooking?

**Definition:** Overbooking is the practice of accepting more reservations than the aircraft's physical capacity to account for passengers who fail to show up for their flights.

**Why Airlines Overbook:**
- Passengers don't show up (no-shows) at a predictable rate (5-15% depending on fare class)
- Empty seats represent lost revenue that cannot be recovered
- Historical data shows overbooking increases revenue by 5-10% on average
- Maximizes load factor (percentage of seats filled)

**Risk:** If more passengers show up than expected, the airline must deny boarding to some passengers and provide compensation.

---

## Implementation Details

### 1. Configuration
Overbooking can be enabled/disabled at multiple levels:
- **Per Flight:** Individual control via admin panel
- **Global:** Enable/disable for all eligible flights at once
- **Automatic Rules:** System enforces safety thresholds

### 2. Key Constraints

#### Industry Standard Limits
- **Maximum Overbooking Percentage:** 15%
- **Minimum Days Before Departure:** 7 days (to enable overbooking)
- **Automatic Disable Window:** 48 hours before departure
- **Hard Stop:** 24 hours before departure (never allow overbooking)

#### Virtual Capacity Calculation
```
Physical Capacity = Aircraft Total Seats (e.g., 180)
Overbooking Percentage = 10%
Virtual Capacity = Physical Capacity × (1 + Overbooking Percentage / 100)
Virtual Capacity = 180 × 1.10 = 198 seats
```

#### Fare Class Considerations
Different fare classes have different no-show rates and overbooking limits:
- **Economy:** 10-15% no-show rate, can overbook up to 15%
- **Business:** 5-8% no-show rate, can overbook up to 10%
- **First Class:** 2-5% no-show rate, can overbook up to 5%

### 3. Safety Thresholds Enforced

The system automatically enforces these safety rules:

1. **Max Percentage Check:**
   - Overbooking percentage cannot exceed 15%
   - Validated in admin panel and API

2. **Time-Based Rules:**
   - Overbooking can only be enabled for flights >7 days away
   - Automatically disabled 48 hours before departure
   - Hard stop at 24 hours - no overbooking allowed

3. **Capacity Limits:**
   - System stops accepting bookings when virtual capacity is reached
   - Example: 198 virtual seats reached → no more bookings accepted

4. **Status Checks:**
   - Overbooking disabled for departed, arrived, or cancelled flights

---

## Booking Flow with Overbooking

### Normal Booking (No Overbooking)
```
Flight: 180 physical seats
Available: Check physical capacity
User books 2 seats → Physical: 178 available
```

### Booking with Overbooking Enabled
```
Flight: 180 physical seats, 10% overbooking = 198 virtual seats
Current bookings: 185 confirmed
Available: 198 - 185 = 13 virtual seats

User books 5 seats:
- Check: 13 virtual seats available ✓
- Booking accepted
- New bookings: 190 confirmed
- Physical overbooked by: 190 - 180 = 10 seats
```

### Virtual Capacity Reached
```
Flight: 180 physical seats, 198 virtual seats
Current bookings: 198 confirmed

User tries to book 1 seat:
- Check: 0 virtual seats available ✗
- Booking REJECTED
- Message: "Flight is fully booked"
```

---

## No-Show Probability

### Historical Data by Fare Class
Based on airline industry averages:

| Fare Class | No-Show Probability | Typical No-Shows (per 100 pax) |
|------------|--------------------|---------------------------------|
| Economy    | 10-15%             | 10-15 passengers                |
| Business   | 5-8%               | 5-8 passengers                  |
| First      | 2-5%               | 2-5 passengers                  |

### Recommended Overbooking Formula
```
Recommended % = (Weighted Avg No-Show Rate) × 0.8

Example:
- Economy (150 seats): 12% no-show
- Business (30 seats): 6% no-show
  
Weighted Average = ((150 × 12) + (30 × 6)) / 180 = 11%
Recommended Overbooking = 11% × 0.8 = 8.8%
```

The 0.8 factor (80%) provides a 20% safety margin to reduce denied boarding risk.

---

## Denied Boarding Handling

### Risk Assessment
The system calculates denied boarding risk:

```
Risk Score = Overbooked Count - Expected No-Shows

Example:
- Physical capacity: 180 seats
- Confirmed bookings: 190 seats
- Overbooked count: 10 seats
- Expected no-shows: 12 passengers
- Risk Score: 10 - 12 = -2

Risk Level: LOW (expected to have 2 empty seats)
```

### Risk Levels
- **None:** Not overbooked (0 overbooked)
- **Low:** Risk score ≤ 0 (expected no-shows cover overbooking)
- **Medium:** Risk score 1-3 (possible 1-3 denied boardings)
- **High:** Risk score > 3 (likely 4+ denied boardings)

### Resolution Process
When confirmed bookings exceed physical capacity at departure:

1. **System Flags Flight:** Status changes to "Overbooked"
2. **Admin Notification:** Alert sent to operations team
3. **Manual Resolution Required:**
   - Request volunteers for alternative flights
   - Offer compensation packages
   - Rebook passengers on next available flight
4. **Compensation Tracking:** System logs denied boarding events with compensation amounts

### Denied Boarding Table
Tracks every denied boarding incident:
- Flight ID
- Booking ID
- User ID
- Fare Class
- Resolution Type (volunteer, involuntary)
- Compensation Amount
- Notes
- Timestamp

---

## Revenue Impact Analysis

### Key Metrics Tracked

1. **Revenue Gained from Overbooking**
   ```
   Revenue Gained = Sum of (Overbooked Seats × Ticket Price) for all flights
   ```

2. **Compensation Costs**
   ```
   Compensation Paid = Sum of all denied boarding compensation
   ```

3. **Net Revenue**
   ```
   Net Revenue = Revenue Gained - Compensation Paid
   ```

4. **Return on Investment (ROI)**
   ```
   ROI = (Net Revenue / Revenue Gained) × 100%
   ```

5. **Load Factor**
   ```
   Load Factor = (Confirmed Bookings / Physical Capacity) × 100%
   Goal: >90% average load factor
   ```

### Expected Outcomes
- **Healthy ROI:** 50-80% ROI indicates optimal overbooking
- **Load Factor Improvement:** Typically increases by 5-10 percentage points
- **Denied Boarding Rate:** Should be <1% of total bookings
- **Customer Satisfaction:** Minimal impact when properly managed

---

## Admin Controls

### Management Interface (`/admin/overbooking`)
Admins can:
- View all flights with overbooking status
- Enable/disable overbooking per flight
- Set overbooking percentage (0-15%)
- View recommended percentages based on no-show data
- Monitor flights at risk of denied boarding
- Access detailed reports and analytics

### Global Operations
- **Enable Globally:** Apply overbooking percentage to all eligible flights
- **Disable Globally:** Turn off overbooking for all flights
- **Bulk Recalculation:** Update overbooked counts system-wide

### At-Risk Dashboard (`/admin/overbooking/at-risk`)
Special view for flights requiring immediate attention:
- Shows flights with confirmed bookings > physical capacity
- Displays risk scores and recommended actions
- Prioritizes flights within 24-48 hours of departure

### Reports & Analytics (`/admin/overbooking/reports`)
Comprehensive reporting dashboard:
- Date range filtering
- KPI cards (load factor, revenue impact, denied boardings)
- Revenue analysis with ROI calculations
- Denied boarding statistics and trends
- Top performing flights
- Export to CSV for further analysis

---

## Best Practices

### When to Enable Overbooking
✅ **DO enable for:**
- Routes with historically high no-show rates
- Flights >7 days away
- Economy-heavy configurations
- Peak season with high demand

❌ **DON'T enable for:**
- Flights <7 days away
- International first-class heavy routes
- VIP/charter flights
- Routes with low no-show history

### Optimal Percentage Guidelines
- **Conservative (5-7%):** Low-risk routes, new routes without data
- **Moderate (8-12%):** Most commercial routes with good data
- **Aggressive (13-15%):** High no-show routes, budget carriers only

### Monitoring Frequency
- **Daily:** Check at-risk flights dashboard
- **Weekly:** Review reports for trends
- **Monthly:** Analyze ROI and adjust strategy
- **Quarterly:** Deep dive on compensation costs vs revenue

---

## Risk Mitigation Strategies

### 1. Predictive Analysis
- Use historical data to predict no-shows
- Adjust percentages based on route performance
- Consider seasonal variations

### 2. Fare Class Balancing
- Lower overbooking for premium classes
- Higher overbooking for budget fares
- Respect per-class limits

### 3. Early Warning System
- Automated alerts for high-risk flights
- 48-hour advance notification to operations
- Proactive volunteer solicitation

### 4. Compensation Planning
- Budget for denied boarding costs
- Maintain relationship with partner hotels
- Have alternative flight options ready

---

## Ethical Considerations

### Transparency
- This is a **SIMULATION SYSTEM**
- In production, clearly inform customers about overbooking policies
- Publish denied boarding compensation policies
- Maintain customer trust through fair practices

### Passenger Rights
In real systems:
- Passengers must be informed of denied boarding rights
- Compensation scales with inconvenience (EU Regulation 261/2004, US DOT rules)
- Volunteers must be solicited before involuntary denial
- Alternative transportation must be provided

### System Labels
All admin interfaces include disclaimers:
- "This is a simulation - no actual denied boarding will occur"
- Educational purposes only
- Real systems require legal compliance and passenger protections

---

## Testing Scenarios

### Test Case 1: Enable Overbooking
```
Given: Flight 7+ days away, 180 seats
When: Admin enables 10% overbooking
Then: Virtual capacity = 198 seats
And: Bookings can exceed 180 up to 198
```

### Test Case 2: Time-Based Auto-Disable
```
Given: Flight with overbooking enabled (10%)
When: Time reaches 48 hours before departure
Then: System auto-disables overbooking
And: Virtual capacity = physical capacity
And: No new bookings beyond physical capacity
```

### Test Case 3: Denied Boarding Scenario
```
Given: Flight 180 seats, 190 confirmed bookings
When: Check-in opens (2 hours before departure)
Then: System flags flight as overbooked (10 seats)
And: Admin dashboard shows high-risk alert
And: Expected no-shows: 12 passengers
And: Risk level: LOW
```

### Test Case 4: Virtual Capacity Limit
```
Given: Flight with 198 virtual seats, 198 bookings
When: User attempts to book 1 more seat
Then: System rejects with "Flight fully booked"
```

---

## Technical Architecture

### Database Schema
- `flights.overbooking_enabled` (boolean)
- `flights.overbooking_percentage` (decimal)
- `flights.overbooked_count` (integer)
- `fare_classes.no_show_probability` (decimal)
- `fare_classes.max_overbooking_percentage` (decimal)
- `denied_boardings` (full table for tracking incidents)

### Service Layer
- `OverbookingService`: Core logic and calculations
- `InventoryService`: Integrates overbooking into seat availability
- `BookingHoldService`: Enforces virtual capacity limits

### Key Methods
- `canOverbook()`: Checks if flight is eligible
- `getVirtualCapacity()`: Calculates bookable seats
- `isAtRiskOfDeniedBoarding()`: Risk assessment
- `calculateRecommendedOverbooking()`: Data-driven suggestions

---

## Compliance & Legal

### Regulatory Requirements (Production Systems)
- **US DOT:** Requires denied boarding compensation ($775-$1550)
- **EU 261/2004:** Compensation €250-€600 depending on distance
- **Voluntary vs Involuntary:** Higher compensation for involuntary
- **Record Keeping:** Must maintain denied boarding statistics

### This System
- Simulation only - no real passenger impact
- Educational/demonstration purposes
- NOT for production use without proper legal review
- Must implement full passenger rights framework for real deployment

---

## Performance Metrics

### Success Indicators
✅ Average load factor >90%
✅ ROI on overbooking >50%
✅ Denied boarding rate <1%
✅ Customer satisfaction maintained
✅ Compensation costs <20% of overbooking revenue

### Warning Signs
⚠️ Load factor <85%
⚠️ Denied boarding rate >2%
⚠️ Negative ROI
⚠️ Customer complaints increasing
⚠️ Compensation exceeding revenue gains

---

## Future Enhancements

### Potential Improvements
1. **Machine Learning:** Predict no-shows using ML models
2. **Dynamic Adjustments:** Real-time percentage optimization
3. **Passenger Segmentation:** Different rules for different customer types
4. **Weather Integration:** Adjust for weather-related no-shows
5. **Route Analysis:** Automatic percentage setting per route

### Integration Possibilities
- CRM system for voluntary bump solicitation
- Partner airline coordination for rebooking
- Hotel booking API for overnight accommodations
- Automated compensation processing

---

## References

### Industry Standards
- IATA Revenue Management Standards
- US Department of Transportation (DOT) Rules
- EU Regulation 261/2004
- Airline Revenue Management (Talluri & van Ryzin)

### Internal Documentation
- `DATABASE_SCHEMA.md` - Database structure
- `TECHNICAL_DOCUMENTATION.md` - System architecture
- `SYSTEM_OVERVIEW.md` - Product overview
- Phase 6 Task Documentation in `MD_FILES/`

---

## Support & Maintenance

### Monitoring
- Daily: At-risk flights dashboard
- Weekly: Performance reports
- Monthly: ROI analysis
- Quarterly: Strategy review

### Troubleshooting
Common issues and solutions documented in technical documentation.

### Contact
For questions about overbooking implementation, refer to technical documentation or system administrators.

---

**Last Updated:** December 2024  
**Version:** 1.0  
**Status:** Production Ready (Simulation)
