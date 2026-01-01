# Testing Guide for Booking History & Profile Enhancements

## 1. Install Dependencies & Run Migrations

```bash
# Install PDF package
composer require barryvdh/laravel-dompdf

# Run migrations
php artisan migrate

# Clear cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 2. Create Test Data (If Needed)

You need some confirmed bookings to test. If you don't have any:

```bash
# Option A: Use tinker to create test bookings
php artisan tinker

# In tinker:
$user = User::first(); // or User::find(YOUR_USER_ID)
$flight = Flight::where('departure_time', '<', now())->first();
$fareClass = FareClass::first();

$booking = Booking::create([
    'user_id' => $user->id,
    'flight_id' => $flight->id,
    'fare_class_id' => $fareClass->id,
    'status' => 'confirmed',
    'locked_price' => 5000,
    'total_price' => 5000,
    'seat_count' => 1,
    'confirmed_at' => now(),
]);

// Create passenger
$booking->passengers()->create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@example.com',
    'phone' => '1234567890',
    'date_of_birth' => '1990-01-01',
]);

exit
```

## 3. Test Features

### A. Profile Dashboard
1. Login to your account
2. Go to: `http://localhost:8000/profile`
3. You should see:
   - Statistics cards (Total Flights, Countries, Miles, Total Spent)
   - Upcoming Trips section (if any)
   - Favorite Routes section (if any)

### B. Booking History Page
1. Click "View Booking History" button on profile page
2. Or go to: `http://localhost:8000/profile/booking-history`
3. You should see:
   - Statistics at top
   - Table of past trips
   - Pagination if > 10 bookings

### C. Download PDF
1. On booking history page, click "Download PDF" button
2. PDF should download with:
   - User info
   - Statistics
   - Table of all past bookings

### D. Export CSV
1. On booking history page, click "Export CSV" button
2. CSV file should download with booking data
3. Open in Excel/Sheets to verify data

### E. Favorite Routes
1. On booking history page, click "Add to Favorites" on any booking
2. Go back to profile page
3. You should see the route in "Favorite Routes" section
4. Click "Quick Book" - should redirect to flight search with origin/destination filled
5. Click X button to remove from favorites

### F. Search Filters (Previous Feature)
1. Go to: `http://localhost:8000/flights/search`
2. Fill in origin/destination/date
3. Click "Advanced Filters"
4. Test each filter:
   - Price range (enter min/max values)
   - Departure time (select morning/afternoon/evening/night)
   - Fare class (select Economy/Business/First)
   - Sort by (test all sorting options)
5. Click "Search" - results should be filtered/sorted correctly

## 4. Quick Test Commands

```bash
# Check routes exist
php artisan route:list | grep profile

# Should show:
# profile.edit
# profile.booking-history
# profile.booking-history.download
# profile.booking-history.export
# profile.favorite-routes.add
# profile.favorite-routes.remove

# Check if dompdf is installed
composer show | grep dompdf
```

## 5. Common Issues & Fixes

### PDF not generating:
```bash
composer require barryvdh/laravel-dompdf
php artisan config:clear
```

### Statistics showing 0:
- You need confirmed bookings with past flight dates
- Create test data using tinker (see step 2)

### Favorite routes not saving:
```bash
php artisan migrate:refresh
# Re-run seeders if needed
```

### CSV download not working:
- Check file permissions on storage folder
- Make sure Laravel can write to temp directory

## 6. URLs to Test

- Profile: `http://localhost:8000/profile`
- Booking History: `http://localhost:8000/profile/booking-history`
- Flight Search: `http://localhost:8000/flights/search`
- Download PDF: `http://localhost:8000/profile/booking-history/download`
- Export CSV: `http://localhost:8000/profile/booking-history/export`

## 7. Expected Results

✅ Profile page shows user statistics
✅ Upcoming trips displayed correctly
✅ Booking history table shows past flights
✅ PDF downloads with correct formatting
✅ CSV exports with all booking data
✅ Favorite routes can be added/removed
✅ Flight search filters work correctly
✅ Flight duration displays correctly
✅ "Clear Filters" button appears when filters active
