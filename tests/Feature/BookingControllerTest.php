<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Flight;
use App\Models\FareClass;
use App\Models\Aircraft;
use App\Models\Booking;
use App\Models\Seat;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingControllerTest extends TestCase
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

        // Create seats
        for ($i = 1; $i <= 3; $i++) {
            Seat::create([
                'flight_id' => $this->flight->id,
                'fare_class_id' => $this->fareClass->id,
                'seat_number' => "{$i}A",
                'status' => 'available',
            ]);
        }
    }

    /** @test */
    public function it_can_create_a_booking_hold()
    {
        $response = $this->actingAs($this->user)
            ->post(route('bookings.create'), [
                'flight_id' => $this->flight->id,
                'fare_class_id' => $this->fareClass->id,
                'seat_count' => 1,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'status' => 'held',
        ]);
    }

    /** @test */
    public function it_prevents_booking_departed_flight()
    {
        $this->flight->update(['departure_time' => Carbon::now()->subHour()]);

        $response = $this->actingAs($this->user)
            ->post(route('bookings.create'), [
                'flight_id' => $this->flight->id,
                'fare_class_id' => $this->fareClass->id,
                'seat_count' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'This flight has already departed.');
    }

    /** @test */
    public function it_prevents_duplicate_holds_for_same_flight()
    {
        // Create first hold
        $booking = Booking::create([
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

        // Try to create second hold
        $response = $this->actingAs($this->user)
            ->post(route('bookings.create'), [
                'flight_id' => $this->flight->id,
                'fare_class_id' => $this->fareClass->id,
                'seat_count' => 1,
            ]);

        $response->assertRedirect(route('bookings.passengers', $booking));
        $response->assertSessionHas('warning');
    }

    /** @test */
    public function it_shows_error_when_not_enough_seats()
    {
        $response = $this->actingAs($this->user)
            ->post(route('bookings.create'), [
                'flight_id' => $this->flight->id,
                'fare_class_id' => $this->fareClass->id,
                'seat_count' => 10, // More than available
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function it_shows_passenger_form_for_held_booking()
    {
        $booking = Booking::create([
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

        $response = $this->actingAs($this->user)
            ->get(route('bookings.passengers', $booking));

        $response->assertStatus(200);
        $response->assertViewIs('bookings.passengers');
        $response->assertViewHas('booking');
    }

    /** @test */
    public function it_redirects_when_viewing_expired_booking()
    {
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

        $response = $this->actingAs($this->user)
            ->get(route('bookings.passengers', $booking));

        $response->assertRedirect(route('flights.search'));
        $response->assertSessionHas('error', 'Your booking has expired. Please search for flights again.');
    }

    /** @test */
    public function it_redirects_confirmed_booking_from_passenger_page()
    {
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'confirmed',
            'locked_price' => 200.00,
            'total_price' => 200.00,
            'seat_count' => 1,
            'held_at' => Carbon::now(),
            'confirmed_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('bookings.passengers', $booking));

        $response->assertRedirect(route('bookings.show', $booking));
    }

    /** @test */
    public function it_shows_payment_page_for_held_booking()
    {
        $booking = Booking::create([
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

        // Create passenger
        $seat = $this->flight->seats()->first();
        $booking->passengers()->create([
            'seat_id' => $seat->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('bookings.payment', $booking));

        $response->assertStatus(200);
        $response->assertViewIs('bookings.payment');
    }

    /** @test */
    public function it_requires_passengers_before_payment()
    {
        $booking = Booking::create([
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

        $response = $this->actingAs($this->user)
            ->get(route('bookings.payment', $booking));

        $response->assertRedirect(route('bookings.passengers', $booking));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function it_shows_confirmation_page_for_confirmed_booking()
    {
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'confirmed',
            'locked_price' => 200.00,
            'total_price' => 200.00,
            'seat_count' => 1,
            'held_at' => Carbon::now(),
            'confirmed_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('bookings.confirmation', $booking));

        $response->assertStatus(200);
        $response->assertViewIs('bookings.confirmation');
    }

    /** @test */
    public function it_redirects_non_confirmed_booking_from_confirmation()
    {
        $booking = Booking::create([
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

        $response = $this->actingAs($this->user)
            ->get(route('bookings.confirmation', $booking));

        $response->assertRedirect(route('bookings.show', $booking));
    }

    /** @test */
    public function it_prevents_unauthorized_access_to_booking()
    {
        $otherUser = User::factory()->create();
        
        $booking = Booking::create([
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

        $response = $this->actingAs($otherUser)
            ->get(route('bookings.show', $booking));

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_cancel_a_booking()
    {
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'confirmed',
            'locked_price' => 200.00,
            'total_price' => 200.00,
            'seat_count' => 1,
            'held_at' => Carbon::now(),
            'confirmed_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('bookings.cancel', $booking));

        $response->assertRedirect(route('bookings.index'));
        $response->assertSessionHas('success');
        
        $this->assertEquals('cancelled', $booking->fresh()->status);
    }

    /** @test */
    public function it_prevents_cancelling_non_cancellable_booking()
    {
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'expired',
            'locked_price' => 200.00,
            'total_price' => 200.00,
            'seat_count' => 1,
            'held_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('bookings.cancel', $booking));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function it_lists_user_bookings()
    {
        Booking::create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'fare_class_id' => $this->fareClass->id,
            'status' => 'confirmed',
            'locked_price' => 200.00,
            'total_price' => 200.00,
            'seat_count' => 1,
            'held_at' => Carbon::now(),
            'confirmed_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('bookings.index'));

        $response->assertStatus(200);
        $response->assertViewIs('bookings.index');
        $response->assertViewHas('bookings');
    }
}
