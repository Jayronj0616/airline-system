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

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'admin_role',
        'is_active',
        'disabled_at',
        'disabled_by',
        'disabled_reason',
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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'disabled_at' => 'datetime',
        'favorite_routes' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function savedPaymentMethods()
    {
        return $this->hasMany(SavedPaymentMethod::class);
    }

    public function defaultPaymentMethod()
    {
        return $this->hasOne(SavedPaymentMethod::class)->where('is_default', true);
    }

    public function auditLogs()
    {
        return $this->hasMany(UserAuditLog::class);
    }

    public function performedAudits()
    {
        return $this->hasMany(UserAuditLog::class, 'performed_by');
    }

    public function disabledBy()
    {
        return $this->belongsTo(User::class, 'disabled_by');
    }

    // Scopes
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDisabled($query)
    {
        return $query->where('is_active', false);
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin()
    {
        return $this->role === 'admin' && $this->admin_role === 'super_admin';
    }

    public function canManageUsers()
    {
        return $this->isSuperAdmin();
    }

    public function canManageFlights()
    {
        return $this->isAdmin() && in_array($this->admin_role, ['super_admin', 'operations']);
    }

    public function canManagePricing()
    {
        return $this->isAdmin() && in_array($this->admin_role, ['super_admin', 'finance']);
    }

    public function canViewReports()
    {
        return $this->isAdmin();
    }

    public function getAdminRoleNameAttribute()
    {
        return match($this->admin_role) {
            'super_admin' => 'Super Admin',
            'operations' => 'Operations',
            'finance' => 'Finance',
            'support' => 'Support',
            default => 'N/A',
        };
    }

    // Previous methods
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

    public function getStatistics()
    {
        $confirmedBookings = $this->bookings()
            ->whereIn('status', ['confirmed', 'confirmed_paid'])
            ->with('flight')
            ->get();

        $totalFlights = $confirmedBookings->count();
        
        $totalMiles = $confirmedBookings->sum(function($booking) {
            return $this->estimateDistance(
                $booking->flight->origin,
                $booking->flight->destination
            );
        });

        $destinations = $confirmedBookings->pluck('flight.destination')->unique();
        $origins = $confirmedBookings->pluck('flight.origin')->unique();
        $uniqueCities = $destinations->merge($origins)->unique()->count();
        $uniqueCountries = ceil($uniqueCities / 3);

        return [
            'total_flights' => $totalFlights,
            'total_miles' => round($totalMiles),
            'countries_visited' => $uniqueCountries,
            'cities_visited' => $uniqueCities,
            'total_spent' => $confirmedBookings->sum('total_price'),
            'member_since' => $this->created_at,
        ];
    }

    private function estimateDistance($origin, $destination)
    {
        $distances = [
            'MNL-HKG' => 700,
            'MNL-SIN' => 1500,
            'MNL-BKK' => 1400,
            'MNL-TPE' => 750,
            'MNL-ICN' => 1600,
        ];

        $key = $origin . '-' . $destination;
        $reverseKey = $destination . '-' . $origin;

        return $distances[$key] ?? $distances[$reverseKey] ?? 1000;
    }

    public function addFavoriteRoute($origin, $destination)
    {
        $favorites = $this->favorite_routes ?? [];
        $route = ['origin' => $origin, 'destination' => $destination];
        
        if (!in_array($route, $favorites)) {
            $favorites[] = $route;
            $this->update(['favorite_routes' => $favorites]);
        }
    }

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
