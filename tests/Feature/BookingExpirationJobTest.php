<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\User;
use App\Models\Flight;
use App\Models\FareClass;
use App\Models\Aircraft;
use App\Models\Seat;
use App\Models\Passenger;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingExpirationJobTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $flight;
    protected $fareClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $aircraft = Aircraft::create([
            'model' => 'Boeing 737',
            'manufacturer' => 'Boeing',
            'total_seats' => 180,
        ]);

        $this->fareClass = FareClass::create([
            'name' => 'Economy',
            'code' => 'Y',
            'description' => 'Economy Class',
        ]);

        $this->flight = Flight::create([
            'flight_number' => 'AA100',
            'aircraft_id' => $aircraft->id,
            'origin' => 'JFK',
            'destination' => 'LAX',
            'departure_time' => Carbon::now()->addDays(7),
            'arrival_time' => Carbon::now()->addDays(7)->addHours(6),
            'base_price' => 200.00,
        ]);
    }

    /** @test */
    public function command_expires_held_bookings_past_expiration_time()
    {
        // Create expired booking
        $seat = Seat::create([
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'seat_number' => '1A',
            'status' => 'held',
        ]);

        $booking = Booking::create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'held',
            'locked_price' => 200.00,
            'total_price' => 200.00,
            'seat_count' => 1,
            'held_at' => Carbon::now()->subMinutes(20),
            'hold_expires_at' => Carbon::now()->subMinutes(5),
        ]);

        Passenger::create([
            'booking_id' => $booking->id,
            'seat_id' => $seat->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
        ]);

        $this->artisan('bookings:release-expired')
            ->expectsOutput('Searching for expired holds...')
            ->assertExitCode(0);

        $this->assertEquals('expired', $booking->fresh()->status);
        $this->assertEquals('available', $seat->fresh()->status);
    }

    /** @test */
    public function command_does_not_expire_active_holds()
    {
        $seat = Seat::create([
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'seat_number' => '1A',
            'status' => 'held',
        ]);

        $booking = Booking::create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'held',
            'locked_price' => 200.00,
            'total_price' => 200.00,
            'seat_count' => 1,
            'held_at' => Carbon::now(),
            'hold_expires_at' => Carbon::now()->addMinutes(10), // Still valid
        ]);

        Passenger::create([
            'booking_id' => $booking->id,
            'seat_id' => $seat->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
        ]);

        $this->artisan('bookings:release-expired')
            ->assertExitCode(0);

        // Should still be held
        $this->assertEquals('held', $booking->fresh()->status);
        $this->assertEquals('held', $seat->fresh()->status);
    }

    /** @test */
    public function command_does_not_expire_confirmed_bookings()
    {
        $seat = Seat::create([
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'seat_number' => '1A',
            'status' => 'booked',
        ]);

        $booking = Booking::create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'confirmed',
            'locked_price' => 200.00,
            'total_price' => 200.00,
            'seat_count' => 1,
            'held_at' => Carbon::now()->subMinutes(20),
            'hold_expires_at' => Carbon::now()->subMinutes(5),
            'confirmed_at' => Carbon::now()->subMinutes(5),
        ]);

        Passenger::create([
            'booking_id' => $booking->id,
            'seat_id' => $seat->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
        ]);

        $this->artisan('bookings:release-expired')
            ->assertExitCode(0);

        // Should still be confirmed
        $this->assertEquals('confirmed', $booking->fresh()->status);
        $this->assertEquals('booked', $seat->fresh()->status);
    }

    /** @test */
    public function command_handles_multiple_expired_bookings()
    {
        // Create 3 expired bookings
        $bookings = [];
        $seats = [];

        for ($i = 1; $i <= 3; $i++) {
            $seat = Seat::create([
                'flight_id' => $this->flight->id,
                'fare_class_id' => $this->fareClass->id,
                'seat_number' => "{$i}A",
                'status' => 'held',
            ]);

            $booking = Booking::create([
                'user_id' => $this->user->id,
                'flight_id' => $this->flight->id,
                'fare_class_id' => $this->fareClass->id,
                'status' => 'held',
                'locked_price' => 200.00,
                'total_price' => 200.00,
                'seat_count' => 1,
                'held_at' => Carbon::now()->subMinutes(20),
                'hold_expires_at' => Carbon::now()->subMinutes(5),
            ]);

            Passenger::create([
                'booking_id' => $booking->id,
                'seat_id' => $seat->id,
                'first_name' => "User{$i}",
                'last_name' => 'Test',
                'email' => "user{$i}@example.com",
                'date_of_birth' => '1990-01-01',
            ]);

            $bookings[] = $booking;
            $seats[] = $seat;
        }

        $this->artisan('bookings:release-expired')
            ->expectsOutput('Found 3 expired hold(s).')
            ->assertExitCode(0);

        // All should be expired
        foreach ($bookings as $booking) {
            $this->assertEquals('expired', $booking->fresh()->status);
        }

        // All seats should be available
        foreach ($seats as $seat) {
            $this->assertEquals('available', $seat->fresh()->status);
        }
    }

    /** @test */
    public function command_supports_dry_run_mode()
    {
        $seat = Seat::create([
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'seat_number' => '1A',
            'status' => 'held',
        ]);

        $booking = Booking::create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'held',
            'locked_price' => 200.00,
            'total_price' => 200.00,
            'seat_count' => 1,
            'held_at' => Carbon::now()->subMinutes(20),
            'hold_expires_at' => Carbon::now()->subMinutes(5),
        ]);

        Passenger::create([
            'booking_id' => $booking->id,
            'seat_id' => $seat->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
        ]);

        $this->artisan('bookings:release-expired --dry-run')
            ->expectsOutput('DRY RUN MODE - No changes will be made')
            ->expectsOutput('Found 1 expired hold(s).')
            ->assertExitCode(0);

        // Should NOT be expired (dry run)
        $this->assertEquals('held', $booking->fresh()->status);
        $this->assertEquals('held', $seat->fresh()->status);
    }

    /** @test */
    public function command_handles_no_expired_bookings()
    {
        $this->artisan('bookings:release-expired')
            ->expectsOutput('No expired holds found.')
            ->assertExitCode(0);
    }

    /** @test */
    public function command_is_scheduled_to_run_every_minute()
    {
        // Check kernel schedule
        $kernel = app(\Illuminate\Console\Scheduling\Schedule::class);
        $events = collect($kernel->events());

        $hasExpireCommand = $events->contains(function ($event) {
            return str_contains($event->command ?? '', 'bookings:release-expired');
        });

        $this->assertTrue($hasExpireCommand, 'Expire command should be scheduled');
    }

    /** @test */
    public function command_releases_seats_for_booking_with_multiple_seats()
    {
        $seats = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $seats[] = Seat::create([
                'flight_id' => $this->flight->id,
                'fare_class_id' => $this->fareClass->id,
                'seat_number' => "1{$i}",
                'status' => 'held',
            ]);
        }

        $booking = Booking::create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'held',
            'locked_price' => 200.00,
            'total_price' => 600.00,
            'seat_count' => 3,
            'held_at' => Carbon::now()->subMinutes(20),
            'hold_expires_at' => Carbon::now()->subMinutes(5),
        ]);

        foreach ($seats as $index => $seat) {
            Passenger::create([
                'booking_id' => $booking->id,
                'seat_id' => $seat->id,
                'first_name' => "Passenger{$index}",
                'last_name' => 'Test',
                'email' => "passenger{$index}@example.com",
                'date_of_birth' => '1990-01-01',
            ]);
        }

        $this->artisan('bookings:release-expired')
            ->expectsOutputToContain('Released 3 seat(s)')
            ->assertExitCode(0);

        // All seats should be released
        foreach ($seats as $seat) {
            $this->assertEquals('available', $seat->fresh()->status);
        }
    }

    /** @test */
    public function command_name_is_correct()
    {
        $this->artisan('list')
            ->expectsOutputToContain('bookings:release-expired');
    }
}
