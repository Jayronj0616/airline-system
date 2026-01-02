<?php
use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\PricingController;
use App\Http\Controllers\Admin\DemandController;
use App\Http\Controllers\Admin\OverbookingController;
use App\Http\Controllers\Admin\OverbookingReportsController;
use App\Http\Controllers\Admin\FareRulesController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Booking History
    Route::get('/profile/booking-history', [ProfileController::class, 'bookingHistory'])->name('profile.booking-history');
    Route::get('/profile/booking-history/download', [ProfileController::class, 'downloadBookingHistory'])->name('profile.booking-history.download');
    Route::get('/profile/booking-history/export', [ProfileController::class, 'exportBookingHistory'])->name('profile.booking-history.export');
    
    // Favorite Routes
    Route::post('/profile/favorite-routes/add', [ProfileController::class, 'addFavoriteRoute'])->name('profile.favorite-routes.add');
    Route::delete('/profile/favorite-routes/remove', [ProfileController::class, 'removeFavoriteRoute'])->name('profile.favorite-routes.remove');
});

// Public flight routes
Route::get('/flights/search', [FlightController::class, 'search'])->name('flights.search');
Route::get('/flights/{flight}', [FlightController::class, 'show'])->name('flights.show');

// Booking flow (guest accessible)
Route::prefix('booking')->name('booking.')->group(function () {
    Route::post('/review-fare', [\App\Http\Controllers\BookingFlowController::class, 'reviewFare'])->name('review-fare');
    Route::post('/create-draft', [\App\Http\Controllers\BookingFlowController::class, 'createDraft'])->name('create-draft');
    Route::get('/{booking}/passengers', [\App\Http\Controllers\BookingFlowController::class, 'passengers'])->name('passengers');
    Route::post('/{booking}/passengers', [\App\Http\Controllers\BookingFlowController::class, 'storePassengers'])->name('passengers.store');
    Route::get('/{booking}/payment', [\App\Http\Controllers\BookingFlowController::class, 'payment'])->name('payment');
    Route::post('/{booking}/payment', [\App\Http\Controllers\BookingFlowController::class, 'processPayment'])->name('payment.process');
    Route::get('/{booking}/confirmation', [\App\Http\Controllers\BookingFlowController::class, 'confirmation'])->name('confirmation');
    Route::get('/{booking}', [\App\Http\Controllers\BookingFlowController::class, 'show'])->name('show');
    
    // Authenticated only
    Route::middleware('auth')->group(function () {
        Route::get('/', [\App\Http\Controllers\BookingFlowController::class, 'index'])->name('index');
        Route::get('/{booking}/select-seats', [\App\Http\Controllers\BookingFlowController::class, 'selectSeats'])->name('select-seats');
        Route::post('/{booking}/store-seats', [\App\Http\Controllers\BookingFlowController::class, 'storeSeats'])->name('store-seats');
        Route::get('/{booking}/add-ons', [\App\Http\Controllers\BookingFlowController::class, 'addOns'])->name('add-ons');
        Route::post('/{booking}/add-ons', [\App\Http\Controllers\BookingFlowController::class, 'storeAddOns'])->name('add-ons.store');
    });
});

// OLD ROUTES - Redirect to new booking flow
Route::redirect('/bookings', '/booking')->name('bookings.index');
Route::get('/bookings/{booking}', function($booking) {
    return redirect('/booking/' . $booking);
})->name('bookings.show');

// Flight Status routes
Route::get('/flight-status', [\App\Http\Controllers\FlightStatusController::class, 'index'])->name('flight-status.index');
Route::get('/flight-status/search', [\App\Http\Controllers\FlightStatusController::class, 'search'])->name('flight-status.search');
Route::get('/flight-status/{flight}', [\App\Http\Controllers\FlightStatusController::class, 'show'])->name('flight-status.show');

// Price Calendar
Route::get('/price-calendar', [\App\Http\Controllers\PriceCalendarController::class, 'show'])->name('price-calendar.show');

// Old booking routes removed - using new booking flow only

//email
Route::get('/send-test-email', function () {
    Mail::to('loki071723@gmail.com')->send(new TestEmail());
    return 'Test email sent!';
});

// Manage Booking routes (guest access)
Route::prefix('manage-booking')->name('manage-booking.')->group(function () {
    Route::get('/', [\App\Http\Controllers\ManageBookingController::class, 'retrieve'])->name('retrieve');
    Route::post('/show', [\App\Http\Controllers\ManageBookingController::class, 'show'])->name('show');
    Route::get('/services', [\App\Http\Controllers\ManageBookingController::class, 'services'])->name('services');
    Route::post('/services', [\App\Http\Controllers\ManageBookingController::class, 'storeServices'])->name('services.store');
    Route::get('/check-in', [\App\Http\Controllers\ManageBookingController::class, 'checkIn'])->name('check-in');
    Route::post('/check-in', [\App\Http\Controllers\ManageBookingController::class, 'processCheckIn'])->name('check-in.process');
    Route::get('/boarding-pass', [\App\Http\Controllers\ManageBookingController::class, 'boardingPass'])->name('boarding-pass');
    Route::get('/boarding-pass/download', [\App\Http\Controllers\ManageBookingController::class, 'downloadBoardingPass'])->name('boarding-pass.download');
    Route::get('/edit-passengers', [\App\Http\Controllers\ManageBookingController::class, 'editPassengers'])->name('edit-passengers');
    Route::post('/edit-passengers', [\App\Http\Controllers\ManageBookingController::class, 'updatePassengers'])->name('update-passengers');
    Route::get('/edit-contact', [\App\Http\Controllers\ManageBookingController::class, 'editContact'])->name('edit-contact');
    Route::post('/edit-contact', [\App\Http\Controllers\ManageBookingController::class, 'updateContact'])->name('update-contact');
});



// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export-csv', [DashboardController::class, 'exportCSV'])->name('dashboard.export-csv');
    
    // Flight Management
    Route::resource('flights', \App\Http\Controllers\Admin\FlightController::class);
    Route::patch('/flights/{flight}/update-status', [\App\Http\Controllers\Admin\FlightController::class, 'updateStatus'])->name('flights.update-status');
    
    // Seat Management
    Route::get('/seats', [\App\Http\Controllers\Admin\SeatManagementController::class, 'index'])->name('seats.index');
    Route::get('/seats/{flight}', [\App\Http\Controllers\Admin\SeatManagementController::class, 'show'])->name('seats.show');
    Route::post('/seats/{seat}/block', [\App\Http\Controllers\Admin\SeatManagementController::class, 'block'])->name('seats.block');
    Route::post('/seats/{seat}/release', [\App\Http\Controllers\Admin\SeatManagementController::class, 'release'])->name('seats.release');
    Route::post('/seats/{flight}/bulk-block', [\App\Http\Controllers\Admin\SeatManagementController::class, 'bulkBlock'])->name('seats.bulk-block');
    Route::post('/seats/{flight}/bulk-release', [\App\Http\Controllers\Admin\SeatManagementController::class, 'bulkRelease'])->name('seats.bulk-release');
    Route::post('/seats/{flight}/upload-map', [\App\Http\Controllers\Admin\SeatManagementController::class, 'uploadSeatMap'])->name('seats.upload-map');
    
    // Booking Management
    Route::get('/bookings', [\App\Http\Controllers\Admin\BookingManagementController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [\App\Http\Controllers\Admin\BookingManagementController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [\App\Http\Controllers\Admin\BookingManagementController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/rebook', [\App\Http\Controllers\Admin\BookingManagementController::class, 'rebook'])->name('bookings.rebook');
    Route::post('/bookings/{booking}/mark-paid', [\App\Http\Controllers\Admin\BookingManagementController::class, 'markAsPaid'])->name('bookings.mark-paid');
    Route::post('/bookings/{booking}/modify', [\App\Http\Controllers\Admin\BookingManagementController::class, 'modify'])->name('bookings.modify');
    
    // Passenger Management
    Route::get('/passengers', [\App\Http\Controllers\Admin\PassengerManagementController::class, 'index'])->name('passengers.index');
    Route::get('/passengers/{passenger}', [\App\Http\Controllers\Admin\PassengerManagementController::class, 'show'])->name('passengers.show');
    Route::get('/passengers/{passenger}/edit', [\App\Http\Controllers\Admin\PassengerManagementController::class, 'edit'])->name('passengers.edit');
    Route::put('/passengers/{passenger}', [\App\Http\Controllers\Admin\PassengerManagementController::class, 'update'])->name('passengers.update');
    Route::get('/passengers/{passenger}/history', [\App\Http\Controllers\Admin\PassengerManagementController::class, 'history'])->name('passengers.history');
    Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.index');
    Route::get('/pricing/{flight}/edit', [PricingController::class, 'edit'])->name('pricing.edit');
    Route::patch('/pricing/{flight}', [PricingController::class, 'update'])->name('pricing.update');
    Route::post('/pricing/{flight}/recalculate', [PricingController::class, 'recalculate'])->name('pricing.recalculate');
    Route::post('/pricing/recalculate-all', [PricingController::class, 'recalculateAll'])->name('pricing.recalculate-all');
    
    // Demand Analytics
    Route::get('/demand', [DemandController::class, 'index'])->name('demand.index');
    
    // Overbooking Management
    Route::get('/overbooking', [OverbookingController::class, 'index'])->name('overbooking.index');
    Route::get('/overbooking/reports', [OverbookingReportsController::class, 'index'])->name('overbooking.reports');
    Route::get('/overbooking/reports/export', [OverbookingReportsController::class, 'export'])->name('overbooking.reports.export');
    Route::get('/overbooking/{flight}/edit', [OverbookingController::class, 'edit'])->name('overbooking.edit');
    Route::post('/overbooking/{flight}/toggle', [OverbookingController::class, 'toggle'])->name('overbooking.toggle');
    Route::post('/overbooking/{flight}/update-percentage', [OverbookingController::class, 'updatePercentage'])->name('overbooking.update-percentage');
    Route::post('/overbooking/enable-global', [OverbookingController::class, 'enableGlobal'])->name('overbooking.enable-global');
    Route::post('/overbooking/disable-global', [OverbookingController::class, 'disableGlobal'])->name('overbooking.disable-global');
    Route::get('/overbooking/at-risk', [OverbookingController::class, 'atRisk'])->name('overbooking.at-risk');
    Route::post('/overbooking/recalculate-all', [OverbookingController::class, 'recalculateAll'])->name('overbooking.recalculate-all');
    
    // Fare Rules Management
    Route::get('/fare-rules', [FareRulesController::class, 'index'])->name('fare-rules.index');
    Route::get('/fare-rules/{fareClass}/edit', [FareRulesController::class, 'edit'])->name('fare-rules.edit');
    Route::patch('/fare-rules/{fareClass}', [FareRulesController::class, 'update'])->name('fare-rules.update');
    
    // Advanced Analytics
    Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
});

require __DIR__.'/auth.php';
