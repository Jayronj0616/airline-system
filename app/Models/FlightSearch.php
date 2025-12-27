<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_id',
        'user_id',
        'origin',
        'destination',
        'search_date',
        'ip_address',
        'searched_at',
    ];

    protected $casts = [
        'search_date' => 'date',
        'searched_at' => 'datetime',
    ];

    /**
     * Get the flight that was searched.
     */
    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    /**
     * Get the user who performed the search.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Boot method to auto-set searched_at.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($flightSearch) {
            if (empty($flightSearch->searched_at)) {
                $flightSearch->searched_at = now();
            }
        });
    }

    /**
     * Log a flight search.
     * 
     * @param int $flightId
     * @param int|null $userId
     * @param array $searchParams
     * @param string|null $ipAddress
     * @return self
     */
    public static function logSearch(int $flightId, ?int $userId = null, array $searchParams = [], ?string $ipAddress = null)
    {
        return self::create([
            'flight_id' => $flightId,
            'user_id' => $userId,
            'origin' => $searchParams['origin'] ?? null,
            'destination' => $searchParams['destination'] ?? null,
            'search_date' => isset($searchParams['date']) ? $searchParams['date'] : null,
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Get recent searches for a flight.
     * 
     * @param int $flightId
     * @param int $hours
     * @return int
     */
    public static function getRecentSearchCount(int $flightId, int $hours = 24)
    {
        return self::where('flight_id', $flightId)
            ->where('searched_at', '>=', now()->subHours($hours))
            ->count();
    }

    /**
     * Get search activity for demand calculation.
     * Returns true if there's recent activity.
     * 
     * @param int $flightId
     * @param int $hours
     * @return bool
     */
    public static function hasRecentActivity(int $flightId, int $hours = 1)
    {
        return self::where('flight_id', $flightId)
            ->where('searched_at', '>=', now()->subHours($hours))
            ->exists();
    }
}
