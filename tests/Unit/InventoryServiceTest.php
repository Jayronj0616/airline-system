<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Flight;
use App\Models\FareClass;
use App\Models\Aircraft;
use App\Models\Seat;
use App\Models\User;
use App\Models\Booking;
use App\Services\InventoryService;
use App\Services\BookingHoldService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $inventoryService;
    protected $user;
    protected $flight;
    protected $fareClass;

    protected function setUp(): void
    {
        parent::setUp();

        // Create services
        $bookingHoldService = new BookingHoldService();
        $pricingService = new PricingService();
        $this->inventoryService = new InventoryService($bookingHoldService, $pricingService);

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
    public function it_gets_available_seats_count()
    {
        $available = $this->inventoryService->getAvailableSeats($this->flight, $this->fareClass);

        $this->assertEquals(10, $available);
    }

    /** @test */
    public function it_gets_flight_availability_breakdown()
    {
        // Book 3 seats
        for ($i = 1; $i <= 3; $i++) {
            Seat::where('seat_number', "{$i}A")->update(['status' => 'booked']);
        }

        // Hold 2 seats
        for ($i = 4; $i <= 5; $i++) {
            Seat::where('seat_number', "{$i}A")->update(['status' => 'held']);
        }

        $availability = $this->inventoryService->getFlightAvailability($this->flight);

        $this->assertArrayHasKey($this->fareClass->id, $availability);
        $this->assertEquals(10, $availability[$this->fareClass->id]['total']);
        $this->assertEquals(5, $availability[$this->fareClass->id]['available']);
        $this->assertEquals(2, $availability[$this->fareClass->id]['held']);
        $this->assertEquals(3, $availability[$this->fareClass->id]['booked']);
        $this->assertEquals(50.0, $availability[$this->fareClass->id]['availability_percent']);
    }

    /** @test */
    public function it_holds_seats_successfully()
    {
        $booking = $this->inventoryService->holdSeats($this->user, $this->flight, $this->fareClass, 2);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals('held', $booking->status);
        $this->assertEquals(2, $booking->seat_count);

        // Check that seats were held
        $heldSeats = Seat::where('flight_id', $this->flight->id)
            ->where('status', 'held')
            ->count();

        $this->assertEquals(2, $heldSeats);
    }

    /** @test */
    public function it_throws_exception_when_holding_more_seats_than_available()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only 10 seat(s) available');

        $this->inventoryService->holdSeats($this->user, $this->flight, $this->fareClass, 20);
    }

    /** @test */
    public function it_releases_expired_holds()
    {
        // Create an expired booking
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'held',
            'locked_price' => 150.00,
            'total_price' => 300.00,
            'seat_count' => 2,
            'held_at' => Carbon::now()->subMinutes(20),
            'hold_expires_at' => Carbon::now()->subMinutes(5), // Expired 5 minutes ago
        ]);

        // Hold some seats
        Seat::where('flight_id', $this->flight->id)
            ->limit(2)
            ->update(['status' => 'held']);

        // Manually create passengers for the booking
        $heldSeats = Seat::where('status', 'held')->limit(2)->get();
        foreach ($heldSeats as $seat) {
            $booking->passengers()->create([
                'seat_id' => $seat->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
            ]);
        }

        // Release expired holds
        $stats = $this->inventoryService->releaseExpiredHolds();

        $this->assertEquals(1, $stats['found']);
        $this->assertEquals(1, $stats['released']);
        $this->assertEquals(2, $stats['seats_freed']);

        // Check booking status changed
        $this->assertEquals('expired', $booking->fresh()->status);

        // Check seats released
        $availableSeats = Seat::where('flight_id', $this->flight->id)
            ->where('status', 'available')
            ->count();

        $this->assertEquals(10, $availableSeats);
    }

    /** @test */
    public function it_confirms_booking_successfully()
    {
        // Create a hold
        $booking = $this->inventoryService->holdSeats($this->user, $this->flight, $this->fareClass, 2);

        // Assign passengers
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

        // Confirm the booking
        $result = $this->inventoryService->confirmBooking($booking);

        $this->assertTrue($result);
        $this->assertEquals('confirmed', $booking->fresh()->status);

        // Check seats are now booked
        $bookedSeats = Seat::where('flight_id', $this->flight->id)
            ->where('status', 'booked')
            ->count();

        $this->assertEquals(2, $bookedSeats);
    }

    /** @test */
    public function it_checks_capacity_correctly()
    {
        $hasCapacity = $this->inventoryService->hasCapacity($this->flight, $this->fareClass, 5);
        $this->assertTrue($hasCapacity);

        $hasCapacity = $this->inventoryService->hasCapacity($this->flight, $this->fareClass, 20);
        $this->assertFalse($hasCapacity);
    }

    /** @test */
    public function it_gets_inventory_summary()
    {
        // Book 3 seats
        Seat::where('flight_id', $this->flight->id)->limit(3)->update(['status' => 'booked']);

        // Hold 2 seats
        Seat::where('flight_id', $this->flight->id)
            ->where('status', 'available')
            ->limit(2)
            ->update(['status' => 'held']);

        $summary = $this->inventoryService->getInventorySummary($this->flight);

        $this->assertEquals(180, $summary['total_seats']);
        $this->assertEquals(5, $summary['available']);
        $this->assertEquals(2, $summary['held']);
        $this->assertEquals(3, $summary['booked']);
        $this->assertGreaterThan(0, $summary['load_factor']);
        $this->assertGreaterThan(0, $summary['utilization']);
    }

    /** @test */
    public function it_gets_bookings_expiring_soon()
    {
        // Create booking expiring in 3 minutes
        Booking::create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'held',
            'locked_price' => 150.00,
            'total_price' => 300.00,
            'seat_count' => 2,
            'held_at' => Carbon::now()->subMinutes(12),
            'hold_expires_at' => Carbon::now()->addMinutes(3),
        ]);

        // Create booking expiring in 10 minutes
        Booking::create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'held',
            'locked_price' => 150.00,
            'total_price' => 300.00,
            'seat_count' => 1,
            'held_at' => Carbon::now()->subMinutes(5),
            'hold_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $expiringSoon = $this->inventoryService->getBookingsExpiringSoon(5);

        // Only the first booking should be expiring within 5 minutes
        $this->assertEquals(1, $expiringSoon->count());
    }

    /** @test */
    public function it_gets_available_seat_list()
    {
        $seats = $this->inventoryService->getAvailableSeatList($this->flight, $this->fareClass);

        $this->assertCount(10, $seats);
        $this->assertEquals('1A', $seats->first()->seat_number);
    }

    /** @test */
    public function it_checks_if_specific_seat_is_available()
    {
        $isAvailable = $this->inventoryService->isSeatAvailable($this->flight, '1A');
        $this->assertTrue($isAvailable);

        // Book the seat
        Seat::where('seat_number', '1A')->update(['status' => 'booked']);

        $isAvailable = $this->inventoryService->isSeatAvailable($this->flight, '1A');
        $this->assertFalse($isAvailable);
    }

    /** @test */
    public function it_gets_seat_map()
    {
        $seatMap = $this->inventoryService->getSeatMap($this->flight);

        $this->assertIsArray($seatMap);
        $this->assertArrayHasKey(1, $seatMap); // Row 1 exists
        $this->assertArrayHasKey('A', $seatMap[1]); // Column A exists in row 1
        $this->assertEquals('1A', $seatMap[1]['A']['seat_number']);
        $this->assertEquals('available', $seatMap[1]['A']['status']);
    }

    /** @test */
    public function it_gets_system_inventory_stats()
    {
        // Book some seats
        Seat::where('flight_id', $this->flight->id)->limit(3)->update(['status' => 'booked']);

        $stats = $this->inventoryService->getSystemInventoryStats();

        $this->assertArrayHasKey('total_seats', $stats);
        $this->assertArrayHasKey('available', $stats);
        $this->assertArrayHasKey('held', $stats);
        $this->assertArrayHasKey('booked', $stats);
        $this->assertEquals(3, $stats['booked']);
    }
}
