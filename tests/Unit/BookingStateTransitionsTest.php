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

class BookingStateTransitionsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $flight;
    protected $fareClass;
    protected $booking;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create test aircraft
        $aircraft = Aircraft::create([
            'model' => 'Boeing 737',
            'manufacturer' => 'Boeing',
            'total_seats' => 180,
        ]);

        // Create fare class
        $this->fareClass = FareClass::create([
            'name' => 'Economy',
            'code' => 'Y',
            'description' => 'Economy Class',
        ]);

        // Create flight
        $this->flight = Flight::create([
            'flight_number' => 'AA100',
            'aircraft_id' => $aircraft->id,
            'origin' => 'JFK',
            'destination' => 'LAX',
            'departure_time' => Carbon::now()->addDays(7),
            'arrival_time' => Carbon::now()->addDays(7)->addHours(6),
            'base_price' => 200.00,
        ]);

        // Create seats
        Seat::create([
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'seat_number' => '1A',
            'status' => 'available',
        ]);

        // Create a held booking
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
    }

    /** @test */
    public function it_can_check_if_booking_is_held()
    {
        $this->assertTrue($this->booking->isHeld());
        $this->assertFalse($this->booking->isConfirmed());
        $this->assertFalse($this->booking->isCancelled());
        $this->assertFalse($this->booking->isExpired());
    }

    /** @test */
    public function it_can_confirm_a_held_booking()
    {
        // Create passenger and seat for the booking
        $seat = $this->booking->flight->seats()->first();
        $seat->hold(15);
        
        Passenger::create([
            'booking_id' => $this->booking->id,
            'seat_id' => $seat->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
        ]);

        $this->booking->confirm();

        $this->assertTrue($this->booking->isConfirmed());
        $this->assertNotNull($this->booking->confirmed_at);
        $this->assertEquals('booked', $seat->fresh()->status);
    }

    /** @test */
    public function it_cannot_confirm_expired_booking()
    {
        $this->booking->update(['hold_expires_at' => Carbon::now()->subMinute()]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot confirm expired booking');

        $this->booking->confirm();
    }

    /** @test */
    public function it_cannot_confirm_non_held_booking()
    {
        $this->booking->update(['status' => 'cancelled']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot confirm booking with status: cancelled');

        $this->booking->confirm();
    }

    /** @test */
    public function it_can_expire_a_held_booking()
    {
        // Create passenger and seat
        $seat = $this->booking->flight->seats()->first();
        $seat->hold(15);
        
        Passenger::create([
            'booking_id' => $this->booking->id,
            'seat_id' => $seat->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
        ]);

        $this->booking->expire();

        $this->assertTrue($this->booking->isExpired());
        $this->assertEquals('available', $seat->fresh()->status);
    }

    /** @test */
    public function it_cannot_expire_non_held_booking()
    {
        $this->booking->update(['status' => 'confirmed', 'confirmed_at' => Carbon::now()]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot expire booking with status: confirmed');

        $this->booking->expire();
    }

    /** @test */
    public function it_can_cancel_a_held_booking()
    {
        // Create passenger and seat
        $seat = $this->booking->flight->seats()->first();
        $seat->hold(15);
        
        Passenger::create([
            'booking_id' => $this->booking->id,
            'seat_id' => $seat->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
        ]);

        $this->booking->cancel('User requested cancellation');

        $this->assertTrue($this->booking->isCancelled());
        $this->assertNotNull($this->booking->cancelled_at);
        $this->assertEquals('User requested cancellation', $this->booking->cancellation_reason);
        $this->assertEquals('available', $seat->fresh()->status);
    }

    /** @test */
    public function it_can_cancel_a_confirmed_booking()
    {
        $this->booking->update([
            'status' => 'confirmed',
            'confirmed_at' => Carbon::now(),
        ]);

        $this->booking->cancel('Change of plans');

        $this->assertTrue($this->booking->isCancelled());
    }

    /** @test */
    public function it_cannot_cancel_already_cancelled_booking()
    {
        $this->booking->update(['status' => 'cancelled', 'cancelled_at' => Carbon::now()]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot cancel booking with status: cancelled');

        $this->booking->cancel();
    }

    /** @test */
    public function it_cannot_cancel_expired_booking()
    {
        $this->booking->update(['status' => 'expired']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot cancel booking with status: expired');

        $this->booking->cancel();
    }

    /** @test */
    public function it_can_check_if_hold_has_expired()
    {
        $this->assertFalse($this->booking->isHoldExpired());

        $this->booking->update(['hold_expires_at' => Carbon::now()->subMinute()]);

        $this->assertTrue($this->booking->isHoldExpired());
    }

    /** @test */
    public function it_can_check_if_booking_can_be_confirmed()
    {
        $this->assertTrue($this->booking->canBeConfirmed());

        // Expired hold
        $this->booking->update(['hold_expires_at' => Carbon::now()->subMinute()]);
        $this->assertFalse($this->booking->canBeConfirmed());

        // Wrong status
        $this->booking->update(['status' => 'confirmed', 'hold_expires_at' => Carbon::now()->addMinutes(10)]);
        $this->assertFalse($this->booking->canBeConfirmed());
    }

    /** @test */
    public function it_can_check_if_booking_can_be_cancelled()
    {
        $this->assertTrue($this->booking->canBeCancelled());

        // Already cancelled
        $this->booking->update(['status' => 'cancelled']);
        $this->assertFalse($this->booking->canBeCancelled());

        // Expired
        $this->booking->update(['status' => 'expired']);
        $this->assertFalse($this->booking->canBeCancelled());
    }

    /** @test */
    public function it_validates_state_transition_rules()
    {
        // held -> confirmed: VALID
        $this->booking->update(['status' => 'held']);
        $this->assertTrue($this->booking->canBeConfirmed());

        // confirmed -> confirmed: INVALID
        $this->booking->update(['status' => 'confirmed', 'confirmed_at' => Carbon::now()]);
        $this->assertFalse($this->booking->canBeConfirmed());

        // expired -> confirmed: INVALID
        $this->booking->update(['status' => 'expired']);
        $this->assertFalse($this->booking->canBeConfirmed());

        // cancelled -> confirmed: INVALID
        $this->booking->update(['status' => 'cancelled']);
        $this->assertFalse($this->booking->canBeConfirmed());
    }

    /** @test */
    public function it_prevents_confirming_booking_for_departed_flight()
    {
        $this->booking->flight->update(['departure_time' => Carbon::now()->subHour()]);
        
        $this->expectException(\Exception::class);
        
        $this->booking->confirm();
    }
}
