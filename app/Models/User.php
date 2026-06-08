<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'city',
        'phone',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function providerProfile()
    {
        return $this->hasOne(ProviderProfile::class, 'user_id');
    }

    /** Services this user offers (as a provider) */
    public function services()
    {
        return $this->hasMany(Service::class, 'provider_id');
    }

    /** Bookings made by this user (as a customer) */
    public function bookingsAsCustomer()
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    /** Bookings received by this user (as a provider) */
    public function bookingsAsProvider()
    {
        return $this->hasMany(Booking::class, 'provider_id');
    }

    /** Weekly availability slots (provider) */
    public function availability()
    {
        return $this->hasMany(Availability::class, 'provider_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isProvider(): bool
    {
        return $this->role === 'provider';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }
}
