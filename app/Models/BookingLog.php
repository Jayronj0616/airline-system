<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'action',
        'description',
        'old_data',
        'new_data',
        'ip_address',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function logAction($bookingId, $action, $description, $oldData = null, $newData = null, $userId = null)
    {
        return self::create([
            'booking_id' => $bookingId,
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => request()->ip(),
        ]);
    }
}
