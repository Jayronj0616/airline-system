<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Flight;
use App\Models\FareClass;
use App\Models\Aircraft;
use App\Models\Seat;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $pricingService;
    protected $aircraft;
    protected $fareClasses;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingService = new PricingService();
        
        // Create test aircraft
        $this->aircraft = Aircraft::create([
            'model' => 'Boeing 737',
            'manufacturer' => 'Boeing',
            'total_seats' => 180,
            'economy_seats' => 150,
            'business_seats' => 24,
            'first_class_seats' => 6,
        ]);

        // Create fare classes
        $this->fareClasses = [
            'economy' => FareClass::create([
                'name' => 'Economy',
                'code' => 'Y',
                'description' => 'Economy Class',
            ]),
            'business' => FareClass::create([
                'name' => 'Business',
                'code' => 'J',
                'description' => 'Business Class',
            ]),
            'first' => FareClass::create([
                'name' => 'First',
                'code' => 'F',
                'description' => 'First Class',
            ]),
        ];
    }

    /** @test */
    public function it_calculates_time_factor_correctly_for_last_minute_booking()
    {
        $flight = Flight::factory()->create([
            'aircraft_id' => $this->aircraft->id,
            'departure_time' => Carbon::now()->addDays(5),
        ]);

        $timeFactor = $this->pricingService->getTimeFactor($flight);
        
        $this->assertEquals(2.0, $timeFactor);
    }

    /** @test */
    public function it_calculates_time_factor_correctly_for_two_week_booking()
    {
        $flight = Flight::factory()->create([
            'aircraft_id' => $this->aircraft->id,
            'departure_time' => Carbon::now()->addDays(10),
        ]);

        $timeFactor = $this->pricingService->getTimeFactor($flight);
        
        $this->assertEquals(1.5, $timeFactor);
    }

    /** @test */
    public function it_calculates_time_factor_correctly_for_early_booking()
    {
        $flight = Flight::factory()->create([
            'aircraft_id' => $this->aircraft->id,
            'departure_time' => Carbon::now()->addDays(45),
        ]);

        $timeFactor = $this->pricingService->getTimeFactor($flight);
        
        $this->assertEquals(1.0, $timeFactor);
    }

    /** @test */
    public function it_calculates_inventory_factor_for_nearly_sold_out_flight()
    {
        $flight = Flight::factory()->create([
            'aircraft_id' => $this->aircraft->id,
        ]);

        // Create 100 seats, make 95 booked (5% available)
        $fareClass = $this->fareClasses['economy'];
        for ($i = 0; $i < 100; $i++) {
            Seat::create([
                'flight_id' => $flight->id,
                'fare_class_id' => $fareClass->id,
                'seat_number' => ($i + 1) . 'A',
                'status' => $i < 95 ? 'booked' : 'available',
            ]);
        }

        $inventoryFactor = $this->pricingService->getInventoryFactor($flight, $fareClass);
        
        $this->assertEquals(1.8, $inventoryFactor);
    }

    /** @test */
    public function it_calculates_inventory_factor_for_half_full_flight()
    {
        $flight = Flight::factory()->create([
            'aircraft_id' => $this->aircraft->id,
        ]);

        // Create 100 seats, make 50 booked (50% available)
        $fareClass = $this->fareClasses['economy'];
        for ($i = 0; $i < 100; $i++) {
            Seat::create([
                'flight_id' => $flight->id,
                'fare_class_id' => $fareClass->id,
                'seat_number' => ($i + 1) . 'A',
                'status' => $i < 50 ? 'booked' : 'available',
            ]);
        }

        $inventoryFactor = $this->pricingService->getInventoryFactor($flight, $fareClass);
        
        $this->assertEquals(1.1, $inventoryFactor);
    }

    /** @test */
    public function it_calculates_demand_factor_for_high_demand()
    {
        $flight = Flight::factory()->create([
            'aircraft_id' => $this->aircraft->id,
            'demand_score' => 90,
        ]);

        $demandFactor = $this->pricingService->getDemandFactor($flight);
        
        $this->assertEquals(1.5, $demandFactor);
    }

    /** @test */
    public function it_calculates_demand_factor_for_medium_demand()
    {
        $flight = Flight::factory()->create([
            'aircraft_id' => $this->aircraft->id,
            'demand_score' => 60,
        ]);

        $demandFactor = $this->pricingService->getDemandFactor($flight);
        
        $this->assertEquals(1.2, $demandFactor);
    }

    /** @test */
    public function it_calculates_demand_factor_for_low_demand()
    {
        $flight = Flight::factory()->create([
            'aircraft_id' => $this->aircraft->id,
            'demand_score' => 30,
        ]);

        $demandFactor = $this->pricingService->getDemandFactor($flight);
        
        $this->assertEquals(1.0, $demandFactor);
    }

    /** @test */
    public function it_calculates_final_price_correctly()
    {
        $flight = Flight::factory()->create([
            'aircraft_id' => $this->aircraft->id,
            'departure_time' => Carbon::now()->addDays(10), // 1.5x time factor
            'base_price_economy' => 100,
            'demand_score' => 60, // 1.2x demand factor
        ]);

        // Create seats: 80% booked (20% available = 1.4x inventory factor)
        $fareClass = $this->fareClasses['economy'];
        for ($i = 0; $i < 100; $i++) {
            Seat::create([
                'flight_id' => $flight->id,
                'fare_class_id' => $fareClass->id,
                'seat_number' => ($i + 1) . 'A',
                'status' => $i < 80 ? 'booked' : 'available',
            ]);
        }

        $price = $this->pricingService->calculateCurrentPrice($flight, $fareClass);
        
        // Expected: 100 × 1.5 × 1.4 × 1.2 = 252
        $this->assertEquals(252.0, $price);
    }

    /** @test */
    public function it_returns_null_for_departed_flight()
    {
        $flight = Flight::factory()->create([
            'aircraft_id' => $this->aircraft->id,
            'departure_time' => Carbon::now()->subDays(1), // Already departed
            'base_price_economy' => 100,
        ]);

        $fareClass = $this->fareClasses['economy'];
        $price = $this->pricingService->calculateCurrentPrice($flight, $fareClass);
        
        $this->assertNull($price);
    }

    /** @test */
    public function it_returns_null_for_sold_out_fare_class()
    {
        $flight = Flight::factory()->create([
            'aircraft_id' => $this->aircraft->id,
            'base_price_economy' => 100,
        ]);

        // Create seats, all booked
        $fareClass = $this->fareClasses['economy'];
        for ($i = 0; $i < 100; $i++) {
            Seat::create([
                'flight_id' => $flight->id,
                'fare_class_id' => $fareClass->id,
                'seat_number' => ($i + 1) . 'A',
                'status' => 'booked',
            ]);
        }

        $price = $this->pricingService->calculateCurrentPrice($flight, $fareClass);
        
        $this->assertNull($price);
    }

    /** @test */
    public function it_records_price_history()
    {
        $flight = Flight::factory()->create([
            'aircraft_id' => $this->aircraft->id,
            'base_price_economy' => 100,
        ]);

        $fareClass = $this->fareClasses['economy'];

        $priceHistory = $this->pricingService->recordPriceHistory($flight, $fareClass, 150.00);

        $this->assertDatabaseHas('price_history', [
            'flight_id' => $flight->id,
            'fare_class_id' => $fareClass->id,
            'price' => 150.00,
        ]);

        $this->assertIsArray($priceHistory->factors_used);
        $this->assertArrayHasKey('time_factor', $priceHistory->factors_used);
        $this->assertArrayHasKey('inventory_factor', $priceHistory->factors_used);
        $this->assertArrayHasKey('demand_factor', $priceHistory->factors_used);
    }

    /** @test */
    public function it_updates_flight_prices_for_all_fare_classes()
    {
        $flight = Flight::factory()->create([
            'aircraft_id' => $this->aircraft->id,
            'base_price_economy' => 100,
            'base_price_business' => 300,
            'base_price_first' => 500,
        ]);

        // Create some seats for each class
        foreach ($this->fareClasses as $fareClass) {
            for ($i = 0; $i < 10; $i++) {
                Seat::create([
                    'flight_id' => $flight->id,
                    'fare_class_id' => $fareClass->id,
                    'seat_number' => ($i + 1) . 'A',
                    'status' => 'available',
                ]);
            }
        }

        $prices = $this->pricingService->updateFlightPrices($flight);

        $this->assertCount(3, $prices);
        $this->assertArrayHasKey($this->fareClasses['economy']->id, $prices);
        $this->assertArrayHasKey($this->fareClasses['business']->id, $prices);
        $this->assertArrayHasKey($this->fareClasses['first']->id, $prices);

        // Verify price history was created
        $this->assertEquals(3, \App\Models\PriceHistory::where('flight_id', $flight->id)->count());
    }
}
