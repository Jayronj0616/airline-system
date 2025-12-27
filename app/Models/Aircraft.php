<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aircraft extends Model
{
    use HasFactory;

    protected $table = 'aircraft';

    protected $fillable = [
        'model',
        'code',
        'total_seats',
        'economy_seats',
        'business_seats',
        'first_class_seats',
    ];

    protected $casts = [
        'total_seats' => 'integer',
        'economy_seats' => 'integer',
        'business_seats' => 'integer',
        'first_class_seats' => 'integer',
    ];

    /**
     * Get all flights using this aircraft.
     */
    public function flights()
    {
        return $this->hasMany(Flight::class);
    }
}
