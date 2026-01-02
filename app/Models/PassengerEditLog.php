<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PassengerEditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'passenger_id',
        'user_id',
        'field_changed',
        'old_value',
        'new_value',
        'reason',
        'ip_address',
    ];

    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function logEdit($passengerId, $field, $oldValue, $newValue, $reason, $userId = null)
    {
        return self::create([
            'passenger_id' => $passengerId,
            'user_id' => $userId ?? auth()->id(),
            'field_changed' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'reason' => $reason,
            'ip_address' => request()->ip(),
        ]);
    }
}
