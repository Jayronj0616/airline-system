<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'date_of_birth',
        'passport_number',
        'nationality',
        'address',
        'city',
        'country',
        'postal_code',
        'favorite_routes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'favorite_routes' => 'array',
    ];

    /**
     * Get all bookings for this user.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get saved payment methods.
     */
    public function savedPaymentMethods()
    {
        return $this->hasMany(SavedPaymentMethod::class);
    }

    /**
     * Get default payment method.
     */
    public function defaultPaymentMethod()
    {
        return $this->hasOne(SavedPaymentMethod::class)->where('is_default', true);
    }

    /**
     * Get confirmed bookings (past trips).
     */
    public function pastTrips()
    {
        return $this->bookings()
            ->whereIn('status', ['confirmed', 'confirmed_paid'])
            ->whereHas('flight', function($q) {
                $q->where('departure_time', '<', now());
            })
            ->with(['flight', 'fareClass', 'passengers'])
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get upcoming bookings.
     */
    public function upcomingTrips()
    {
        return $this->bookings()
            ->whereIn('status', ['confirmed', 'confirmed_paid', 'held'])
            ->whereHas('flight', function($q) {
                $q->where('departure_time', '>=', now());
            })
            ->with(['flight', 'fareClass', 'passengers'])
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get user statistics.
     */
    public function getStatistics()
    {
        $confirmedBookings = $this->bookings()
            ->whereIn('status', ['confirmed', 'confirmed_paid'])
            ->with('flight')
            ->get();

        $totalFlights = $confirmedBookings->count();
        
        // Calculate total distance (estimated)
        $totalMiles = $confirmedBookings->sum(function($booking) {
            return $this->estimateDistance(
                $booking->flight->origin,
                $booking->flight->destination
            );
        });

        // Unique countries and cities visited
        $destinations = $confirmedBookings->pluck('flight.destination')->unique();
        $origins = $confirmedBookings->pluck('flight.origin')->unique();
        $uniqueCities = $destinations->merge($origins)->unique()->count();

        // Estimate countries (simplified - in real app, use proper mapping)
        $uniqueCountries = ceil($uniqueCities / 3); // Rough estimate

        return [
            'total_flights' => $totalFlights,
            'total_miles' => round($totalMiles),
            'countries_visited' => $uniqueCountries,
            'cities_visited' => $uniqueCities,
            'total_spent' => $confirmedBookings->sum('total_price'),
            'member_since' => $this->created_at,
        ];
    }

    /**
     * Estimate distance between two airports (simplified).
     * In production, use actual airport coordinates and haversine formula.
     */
    private function estimateDistance($origin, $destination)
    {
        // Simplified estimation based on airport codes
        // In production, use actual lat/long coordinates
        $distances = [
            'MNL-HKG' => 700,
            'MNL-SIN' => 1500,
            'MNL-BKK' => 1400,
            'MNL-TPE' => 750,
            'MNL-ICN' => 1600,
        ];

        $key = $origin . '-' . $destination;
        $reverseKey = $destination . '-' . $origin;

        return $distances[$key] ?? $distances[$reverseKey] ?? 1000; // Default 1000 miles
    }

    /**
     * Add route to favorites.
     */
    public function addFavoriteRoute($origin, $destination)
    {
        $favorites = $this->favorite_routes ?? [];
        $route = ['origin' => $origin, 'destination' => $destination];
        
        if (!in_array($route, $favorites)) {
            $favorites[] = $route;
            $this->update(['favorite_routes' => $favorites]);
        }
    }

    /**
     * Remove route from favorites.
     */
    public function removeFavoriteRoute($origin, $destination)
    {
        $favorites = $this->favorite_routes ?? [];
        $route = ['origin' => $origin, 'destination' => $destination];
        
        $favorites = array_filter($favorites, function($fav) use ($route) {
            return $fav !== $route;
        });
        
        $this->update(['favorite_routes' => array_values($favorites)]);
    }
}
