<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddOnAvailability extends Model
{
    use HasFactory;

    protected $table = 'add_on_availability';

    protected $fillable = [
        'add_on_id',
        'route_origin',
        'route_destination',
        'fare_class_id',
        'price_override',
        'is_available',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function addOn()
    {
        return $this->belongsTo(AddOn::class);
    }

    public function fareClass()
    {
        return $this->belongsTo(FareClass::class);
    }
}
