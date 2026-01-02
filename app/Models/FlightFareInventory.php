<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightFareInventory extends Model
{
    use HasFactory;

    protected $table = 'flight_fare_inventory';

    protected $fillable = [
        'flight_id',
        'fare_class_id',
        'total_seats',
        'available_seats',
        'booked_seats',
        'held_seats',
    ];

    protected $casts = [
        'total_seats' => 'integer',
        'available_seats' => 'integer',
        'booked_seats' => 'integer',
        'held_seats' => 'integer',
    ];

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    public function fareClass()
    {
        return $this->belongsTo(FareClass::class);
    }

    public function canBook($quantity = 1)
    {
        return $this->available_seats >= $quantity;
    }

    public function holdSeats($quantity)
    {
        if (!$this->canBook($quantity)) {
            return false;
        }

        $this->decrement('available_seats', $quantity);
        $this->increment('held_seats', $quantity);
        
        return true;
    }

    public function releaseSeats($quantity)
    {
        $this->increment('available_seats', $quantity);
        $this->decrement('held_seats', $quantity);
    }

    public function confirmSeats($quantity)
    {
        $this->decrement('held_seats', $quantity);
        $this->increment('booked_seats', $quantity);
    }

    public function getLoadFactorAttribute()
    {
        if ($this->total_seats == 0) return 0;
        return round(($this->booked_seats / $this->total_seats) * 100, 2);
    }
}
