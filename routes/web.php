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
});

// Public flight routes
Route::get('/flights/search', [FlightController::class, 'search'])->name('flights.search');
Route::get('/flights/{flight}', [FlightController::class, 'show'])->name('flights.show');

// Flight Status routes
Route::get('/flight-status', [\App\Http\Controllers\FlightStatusController::class, 'index'])->name('flight-status.index');
Route::get('/flight-status/search', [\App\Http\Controllers\FlightStatusController::class, 'search'])->name('flight-status.search');
Route::get('/flight-status/{flight}', [\App\Http\Controllers\FlightStatusController::class, 'show'])->name('flight-status.show');

// Price Calendar
Route::get('/price-calendar', [\App\Http\Controllers\PriceCalendarController::class, 'show'])->name('price-calendar.show');

// Booking routes (authentication required)
Route::middleware(['auth'])->prefix('bookings')->name('bookings.')->group(function () {
    Route::get('/', [BookingController::class, 'index'])->name('index');
    Route::post('/create', [BookingController::class, 'create'])->name('create');
    Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
    
    // Passenger information
    Route::get('/{booking}/passengers', [BookingController::class, 'passengers'])->name('passengers');
    Route::post('/{booking}/passengers', [BookingController::class, 'storePassengers'])->name('passengers.store');
    
    // Seat selection
    Route::get('/{booking}/seats', [BookingController::class, 'seats'])->name('seats');
    Route::post('/{booking}/seats', [BookingController::class, 'storeSeats'])->name('seats.store');
    
    // Payment
    Route::get('/{booking}/payment', [BookingController::class, 'payment'])->name('payment');
    Route::post('/{booking}/payment', [BookingController::class, 'processPayment'])->name('payment.process');
    
    // Confirmation
    Route::get('/{booking}/confirmation', [BookingController::class, 'confirmation'])->name('confirmation');
    
    // Cancellation, Refund, and Changes
    Route::get('/{booking}/cancel', [BookingController::class, 'showCancelForm'])->name('cancel.form');
    Route::delete('/{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');
    Route::post('/{booking}/refund', [BookingController::class, 'requestRefund'])->name('refund');
    Route::post('/{booking}/change', [BookingController::class, 'requestChange'])->name('change');
});

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
});

require __DIR__.'/auth.php';
