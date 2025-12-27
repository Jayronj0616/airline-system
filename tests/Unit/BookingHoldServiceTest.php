<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Flight;
use App\Models\FareClass;
use App\Models\Aircraft;
use App\Models\Seat;
use App\Models\User;
use App\Models\Booking;
use App\Services\BookingHoldService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingHoldServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $holdService;
    protected $user;
    protected $flight;
    protected $fareClass;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->holdService = new BookingHoldService();

        // Create test user
        $this->user = User::factory()->create();

        // Create test aircraft
        $aircraft = Aircraft::create([
            'model' => 'Boeing 737',
            'manufacturer' => 'Boeing',
            'total_seats' => 180,
            'economy_seats' => 150,
            'business_seats' => 24,
            'first_class_seats' => 6,
        ]);

        // Create fare class
        $this->fareClass = FareClass::create([
            'name' => 'Economy',
            'code' => 'Y',
            'description' => 'Economy Class',
        ]);

        // Create test flight
        $this->flight = Flight::factory()->create([
            'aircraft_id' => $aircraft->id,
            'departure_time' => Carbon::now()->addDays(10),
            'base_price_economy' => 100,
        ]);

        // Create some available seats
        for ($i = 1; $i <= 10; $i++) {
            Seat::create([
                'flight_id' => $this->flight->id,
                'fare_class_id' => $this->fareClass->id,
                'seat_number' => "{$i}A",
                'status' => 'available',
            ]);
        }
    }

    /** @test */
    public function it_creates_a_booking_hold_successfully()
    {
        $lockedPrice = 150.00;
        $seatCount = 2;

        $booking = $this->holdService->createHold(
            $this->user,
            $this->flight,
            $this->fareClass,
            $seatCount,
            $lockedPrice
        );

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals('held', $booking->status);
        $this->assertEquals($lockedPrice, $booking->locked_price);
        $this->assertEquals($lockedPrice * $seatCount, $booking->total_price);
        $this->assertEquals($seatCount, $booking->seat_count);
        $this->assertNotNull($booking->hold_expires_at);

        // Check that seats were held
        $heldSeats = Seat::where('flight_id', $this->flight->id)
            ->where('status', 'held')
            ->count();
        
        $this->assertEquals($seatCount, $heldSeats);
    }

    /** @test */
    public function it_throws_exception_when_not_enough_seats_available()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not enough seats available');

        // Try to book more seats than available
        $this->holdService->createHold(
            $this->user,
            $this->flight,
            $this->fareClass,
            20, // Only 10 seats exist
            150.00
        );
    }

    /** @test */
    public function it_releases_hold_successfully()
    {
        // Create a hold
        $booking = $this->holdService->createHold(
            $this->user,
            $this->flight,
            $this->fareClass,
            2,
            150.00
        );

        // Get the held seats
        $heldSeats = Seat::where('flight_id', $this->flight->id)
            ->where('status', 'held')
            ->get();

        // Manually assign seats to passengers (normally done in booking flow)
        foreach ($heldSeats as $seat) {
            $booking->passengers()->create([
                'seat_id' => $seat->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
            ]);
        }

        // Release the hold
        $result = $this->holdService->releaseHold($booking);

        $this->assertTrue($result);
        $this->assertEquals('expired', $booking->fresh()->status);

        // Check that seats are available again
        $availableSeats = Seat::where('flight_id', $this->flight->id)
            ->where('status', 'available')
            ->count();
        
        $this->assertEquals(10, $availableSeats);
    }

    /** @test */
    public function it_confirms_hold_successfully()
    {
        // Create a hold
        $booking = $this->holdService->createHold(
            $this->user,
            $this->flight,
            $this->fareClass,
            2,
            150.00
        );

        // Get the held seats and assign to passengers
        $heldSeats = Seat::where('flight_id', $this->flight->id)
            ->where('status', 'held')
            ->get();

        foreach ($heldSeats as $seat) {
            $booking->passengers()->create([
                'seat_id' => $seat->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
            ]);
        }

        // Confirm the hold
        $result = $this->holdService->confirmHold($booking);

        $this->assertTrue($result);
        $this->assertEquals('confirmed', $booking->fresh()->status);
        $this->assertNotNull($booking->fresh()->confirmed_at);

        // Check that seats are now booked
        $bookedSeats = Seat::where('flight_id', $this->flight->id)
            ->where('status', 'booked')
            ->count();
        
        $this->assertEquals(2, $bookedSeats);
    }

    /** @test */
    public function it_throws_exception_when_confirming_expired_hold()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Booking hold has expired');

        // Create a hold with past expiration
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'held',
            'locked_price' => 150.00,
            'total_price' => 300.00,
            'seat_count' => 2,
            'held_at' => Carbon::now()->subMinutes(20),
            'hold_expires_at' => Carbon::now()->subMinutes(5), // Already expired
        ]);

        $this->holdService->confirmHold($booking);
    }

    /** @test */
    public function it_detects_active_hold_for_same_flight()
    {
        // Create a hold
        $this->holdService->createHold(
            $this->user,
            $this->flight,
            $this->fareClass,
            2,
            150.00
        );

        // Check if user has active hold
        $hasHold = $this->holdService->hasActiveHold($this->user, $this->flight);

        $this->assertTrue($hasHold);
    }

    /** @test */
    public function it_calculates_remaining_time_correctly()
    {
        // Create a hold
        $booking = $this->holdService->createHold(
            $this->user,
            $this->flight,
            $this->fareClass,
            2,
            150.00
        );

        $remainingTime = $this->holdService->getRemainingTime($booking);

        // Should be approximately 15 minutes (allowing for 1 minute variance)
        $this->assertGreaterThanOrEqual(14, $remainingTime);
        $this->assertLessThanOrEqual(15, $remainingTime);
    }

    /** @test */
    public function it_extends_hold_duration()
    {
        // Create a hold
        $booking = $this->holdService->createHold(
            $this->user,
            $this->flight,
            $this->fareClass,
            2,
            150.00
        );

        $originalExpiry = $booking->hold_expires_at;

        // Extend the hold by 15 minutes
        $result = $this->holdService->extendHold($booking, 15);

        $this->assertTrue($result);

        $newExpiry = $booking->fresh()->hold_expires_at;
        
        $this->assertGreaterThan($originalExpiry, $newExpiry);
    }

    /** @test */
    public function it_returns_hold_statistics()
    {
        // Create some holds
        $this->holdService->createHold($this->user, $this->flight, $this->fareClass, 2, 150.00);
        
        $user2 = User::factory()->create();
        $this->holdService->createHold($user2, $this->flight, $this->fareClass, 1, 150.00);

        $stats = $this->holdService->getHoldStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('active_holds', $stats);
        $this->assertArrayHasKey('total_held_seats', $stats);
        $this->assertEquals(2, $stats['active_holds']);
        $this->assertEquals(3, $stats['total_held_seats']);
    }

    /** @test */
    public function it_prevents_race_condition_with_pessimistic_locking()
    {
        // This test simulates two users trying to book the last seat simultaneously
        // Due to lockForUpdate(), only one should succeed

        // Create only 1 available seat
        $onlyOneSeat = Seat::where('flight_id', $this->flight->id)->first();
        Seat::where('flight_id', $this->flight->id)
            ->where('id', '!=', $onlyOneSeat->id)
            ->delete();

        $user2 = User::factory()->create();

        try {
            // First user books
            $booking1 = $this->holdService->createHold(
                $this->user,
                $this->flight,
                $this->fareClass,
                1,
                150.00
            );

            // Second user tries to book the same seat
            $booking2 = $this->holdService->createHold(
                $user2,
                $this->flight,
                $this->fareClass,
                1,
                150.00
            );

            // If we get here, test should fail because second booking shouldn't succeed
            $this->fail('Second booking should have thrown an exception');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Not enough seats available', $e->getMessage());
        }

        // Verify only one booking exists
        $this->assertEquals(1, Booking::where('flight_id', $this->flight->id)->count());
    }
}
