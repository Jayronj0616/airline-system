<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeniedBoarding extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_id',
        'booking_id',
        'user_id',
        'fare_class_id',
        'resolution_type',
        'compensation_amount',
        'notes',
        'denied_at',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'compensation_amount' => 'decimal:2',
        'denied_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the flight this denial is for.
     */
    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    /**
     * Get the booking that was denied.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user who was denied boarding.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the fare class.
     */
    public function fareClass()
    {
        return $this->belongsTo(FareClass::class);
    }

    /**
     * Get the admin who resolved this denial.
     */
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Check if denial has been resolved.
     * 
     * @return bool
     */
    public function isResolved(): bool
    {
        return $this->resolution_type !== 'pending' && $this->resolved_at !== null;
    }

    /**
     * Mark as resolved by volunteer.
     * 
     * @param int $resolvedBy Admin user ID
     * @param float $compensationAmount
     * @param string|null $notes
     * @return void
     */
    public function resolveAsVolunteer(int $resolvedBy, float $compensationAmount, ?string $notes = null): void
    {
        $this->update([
            'resolution_type' => 'volunteer',
            'compensation_amount' => $compensationAmount,
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy,
            'notes' => $notes ? $this->notes . "\n" . $notes : $this->notes,
        ]);
    }

    /**
     * Mark as resolved involuntarily.
     * 
     * @param int $resolvedBy Admin user ID
     * @param float $compensationAmount
     * @param string|null $notes
     * @return void
     */
    public function resolveAsInvoluntary(int $resolvedBy, float $compensationAmount, ?string $notes = null): void
    {
        $this->update([
            'resolution_type' => 'involuntary',
            'compensation_amount' => $compensationAmount,
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy,
            'notes' => $notes ? $this->notes . "\n" . $notes : $this->notes,
        ]);
    }

    /**
     * Scope for pending denials.
     */
    public function scopePending($query)
    {
        return $query->where('resolution_type', 'pending');
    }

    /**
     * Scope for resolved denials.
     */
    public function scopeResolved($query)
    {
        return $query->where('resolution_type', '!=', 'pending')
                    ->whereNotNull('resolved_at');
    }

    /**
     * Get status badge color.
     * 
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->resolution_type) {
            'volunteer' => 'green',
            'involuntary' => 'red',
            'pending' => 'yellow',
            default => 'gray',
        };
    }
}
