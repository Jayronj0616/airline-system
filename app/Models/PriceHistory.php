<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;

    protected $table = 'price_history';

    protected $fillable = [
        'flight_id',
        'fare_class_id',
        'price',
        'factors',
        'time_factor',
        'inventory_factor',
        'demand_factor',
        'recorded_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'factors' => 'array',
        'time_factor' => 'decimal:2',
        'inventory_factor' => 'decimal:2',
        'demand_factor' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    /**
     * Get the flight this price belongs to.
     */
    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    /**
     * Get the fare class this price belongs to.
     */
    public function fareClass()
    {
        return $this->belongsTo(FareClass::class);
    }

    /**
     * Boot method to auto-set recorded_at.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($priceHistory) {
            if (empty($priceHistory->recorded_at)) {
                $priceHistory->recorded_at = now();
            }
        });
    }
}
