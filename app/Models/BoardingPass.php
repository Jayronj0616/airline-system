<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BoardingPass extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'passenger_id',
        'barcode',
        'gate',
        'boarding_time',
        'boarding_group',
    ];

    protected $casts = [
        'boarding_time' => 'datetime',
    ];

    /**
     * Boot method to auto-generate barcode.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($boardingPass) {
            if (empty($boardingPass->barcode)) {
                $boardingPass->barcode = self::generateBarcode();
            }
        });
    }

    /**
     * Generate unique barcode.
     */
    protected static function generateBarcode()
    {
        do {
            $barcode = strtoupper(Str::random(12));
        } while (self::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Get the booking for this boarding pass.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the passenger for this boarding pass.
     */
    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }
}
