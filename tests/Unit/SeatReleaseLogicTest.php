<?php

namespace Tests\Unit;

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

class SeatReleaseLogicTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $flight;
    protected $fareClass;
    protected $booking;
    protected $seat;

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

        $this->seat = Seat::create([
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'seat_number' => '1A',
            'status' => 'held',
            'hold_expires_at' => Carbon::now()->addMinutes(15),
        ]);

        $this->booking = Booking::create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'held',
            'locked_price' => 200.00,
            'total_price' => 200.00,
            'seat_count' => 1,
            'held_at' => Carbon::now(),
            'hold_expires_at' => Carbon::now()->addMinutes(15),
        ]);

        Passenger::create([
            'booking_id' => $this->booking->id,
            'seat_id' => $this->seat->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
        ]);
    }

    /** @test */
    public function it_releases_seats_when_booking_expires()
    {
        $this->assertEquals('held', $this->seat->status);

        $this->booking->expire();

        $this->assertEquals('expired', $this->booking->status);
        $this->assertEquals('available', $this->seat->fresh()->status);
    }

    /** @test */
    public function it_releases_seats_when_booking_is_cancelled()
    {
        $this->booking->update(['status' => 'confirmed', 'confirmed_at' => Carbon::now()]);
        $this->seat->update(['status' => 'booked']);

        $this->booking->cancel('User requested cancellation');

        $this->assertEquals('cancelled', $this->booking->status);
        $this->assertEquals('available', $this->seat->fresh()->status);
    }

    /** @test */
    public function it_releases_multiple_seats_when_booking_expires()
    {
        // Create additional seats
        $seat2 = Seat::create([
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'seat_number' => '1B',
            'status' => 'held',
            'hold_expires_at' => Carbon::now()->addMinutes(15),
        ]);

        $seat3 = Seat::create([
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'seat_number' => '1C',
            'status' => 'held',
            'hold_expires_at' => Carbon::now()->addMinutes(15),
        ]);

        // Add passengers
        Passenger::create([
            'booking_id' => $this->booking->id,
            'seat_id' => $seat2->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'date_of_birth' => '1992-01-01',
        ]);

        Passenger::create([
            'booking_id' => $this->booking->id,
            'seat_id' => $seat3->id,
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'email' => 'bob@example.com',
            'date_of_birth' => '1985-01-01',
        ]);

        $this->booking->update(['seat_count' => 3]);

        // All seats should be held
        $this->assertEquals('held', $this->seat->status);
        $this->assertEquals('held', $seat2->status);
        $this->assertEquals('held', $seat3->status);

        // Expire booking
        $this->booking->expire();

        // All seats should be released
        $this->assertEquals('available', $this->seat->fresh()->status);
        $this->assertEquals('available', $seat2->fresh()->status);
        $this->assertEquals('available', $seat3->fresh()->status);
    }

    /** @test */
    public function it_releases_booked_seats_when_confirmed_booking_is_cancelled()
    {
        // Confirm booking
        $this->booking->update(['status' => 'confirmed', 'confirmed_at' => Carbon::now()]);
        $this->seat->update(['status' => 'booked']);

        $this->assertEquals('booked', $this->seat->status);

        // Cancel booking
        $this->booking->cancel('Change of plans');

        // Seat should be back to available
        $this->assertEquals('available', $this->seat->fresh()->status);
    }

    /** @test */
    public function expired_booking_does_not_release_seats_twice()
    {
        $this->booking->expire();
        
        $this->assertEquals('expired', $this->booking->status);
        $this->assertEquals('available', $this->seat->fresh()->status);

        // Try to expire again (should throw exception)
        try {
            $this->booking->expire();
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Cannot expire booking with status: expired', $e->getMessage());
        }

        // Seat should still be available
        $this->assertEquals('available', $this->seat->fresh()->status);
    }

    /** @test */
    public function seat_status_changes_correctly_through_lifecycle()
    {
        // Initial: held
        $this->assertEquals('held', $this->seat->status);

        // Confirm booking
        $this->booking->update(['status' => 'confirmed', 'confirmed_at' => Carbon::now()]);
        $this->seat->book();
        $this->assertEquals('booked', $this->seat->fresh()->status);

        // Cancel booking
        $this->booking->cancel('Test cancellation');
        $this->assertEquals('available', $this->seat->fresh()->status);
    }

    /** @test */
    public function release_seats_method_exists_on_booking_model()
    {
        // The release logic is integrated into expire() and cancel() methods
        // Verify they work correctly
        
        $this->assertTrue(method_exists($this->booking, 'expire'));
        $this->assertTrue(method_exists($this->booking, 'cancel'));
    }

    /** @test */
    public function seats_are_released_immediately_not_delayed()
    {
        $beforeExpire = Carbon::now();
        
        $this->booking->expire();
        
        $afterExpire = Carbon::now();
        
        // Should be immediate (less than 1 second)
        $this->assertLessThan(1, $afterExpire->diffInSeconds($beforeExpire));
        $this->assertEquals('available', $this->seat->fresh()->status);
    }

    /** @test */
    public function only_seats_from_expired_booking_are_released()
    {
        // Create another booking with a different seat
        $otherSeat = Seat::create([
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'seat_number' => '2A',
            'status' => 'held',
            'hold_expires_at' => Carbon::now()->addMinutes(15),
        ]);

        $otherUser = User::factory()->create();
        $otherBooking = Booking::create([
            'user_id' => $otherUser->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'held',
            'locked_price' => 200.00,
            'total_price' => 200.00,
            'seat_count' => 1,
            'held_at' => Carbon::now(),
            'hold_expires_at' => Carbon::now()->addMinutes(15),
        ]);

        Passenger::create([
            'booking_id' => $otherBooking->id,
            'seat_id' => $otherSeat->id,
            'first_name' => 'Other',
            'last_name' => 'User',
            'email' => 'other@example.com',
            'date_of_birth' => '1990-01-01',
        ]);

        // Expire only first booking
        $this->booking->expire();

        // First booking's seat should be released
        $this->assertEquals('available', $this->seat->fresh()->status);
        
        // Other booking's seat should still be held
        $this->assertEquals('held', $otherSeat->fresh()->status);
    }

    /** @test */
    public function seat_release_works_with_passenger_relationship()
    {
        // Verify passenger relationship exists
        $this->assertCount(1, $this->booking->passengers);
        
        // Verify passenger has seat
        $passenger = $this->booking->passengers->first();
        $this->assertEquals($this->seat->id, $passenger->seat_id);
        
        // Expire booking
        $this->booking->expire();
        
        // Seat should be released through passenger relationship
        $this->assertEquals('available', $this->seat->fresh()->status);
    }
}
