<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddOn extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'base_price',
        'is_active',
        'max_quantity',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
        'max_quantity' => 'integer',
    ];

    public function availability()
    {
        return $this->hasMany(AddOnAvailability::class);
    }

    public function bookingAddOns()
    {
        return $this->hasMany(BookingAddOn::class);
    }

    public function isAvailableForRoute($origin, $destination, $fareClassId = null)
    {
        if (!$this->is_active) {
            return false;
        }

        $query = $this->availability()
            ->where('is_available', true);

        // Check for specific route
        $specificRoute = (clone $query)
            ->where('route_origin', $origin)
            ->where('route_destination', $destination)
            ->when($fareClassId, fn($q) => $q->where('fare_class_id', $fareClassId))
            ->first();

        if ($specificRoute) {
            return true;
        }

        // Check if no restrictions (available for all)
        if ($this->availability()->count() === 0) {
            return true;
        }

        return false;
    }

    public function getPriceForRoute($origin, $destination, $fareClassId = null)
    {
        $availability = $this->availability()
            ->where('route_origin', $origin)
            ->where('route_destination', $destination)
            ->when($fareClassId, fn($q) => $q->where('fare_class_id', $fareClassId))
            ->first();

        return $availability?->price_override ?? $this->base_price;
    }
}
