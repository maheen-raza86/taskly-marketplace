<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'provider_id',
        'service_id',
        'booking_date',
        'time_slot',
        'status',
        'notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function review()
    {
        return $this->hasOne(Review::class, 'booking_id');
    }
}
