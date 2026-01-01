<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedPaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_type',
        'card_brand',
        'last_four',
        'token',
        'cardholder_name',
        'expiry_month',
        'expiry_year',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns this payment method.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get masked card number.
     */
    public function getMaskedNumberAttribute()
    {
        return '**** **** **** ' . $this->last_four;
    }

    /**
     * Get expiry string.
     */
    public function getExpiryAttribute()
    {
        if (!$this->expiry_month || !$this->expiry_year) {
            return null;
        }
        return str_pad($this->expiry_month, 2, '0', STR_PAD_LEFT) . '/' . $this->expiry_year;
    }

    /**
     * Check if card is expired.
     */
    public function isExpired()
    {
        if (!$this->expiry_month || !$this->expiry_year) {
            return false;
        }
        
        $expiryDate = \Carbon\Carbon::createFromDate($this->expiry_year, $this->expiry_month, 1)->endOfMonth();
        return $expiryDate->isPast();
    }
}
