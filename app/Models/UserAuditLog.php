<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'performed_by',
        'action',
        'old_value',
        'new_value',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
