<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_subscribed' => 'boolean',
            'wallet_balance' => 'double',
            'rating' => 'double',
            'border_points' => 'integer',
        ];
    }

    public function trips()
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }

    public function bookings()
    {
        return $this->hasMany(TripBooking::class, 'passenger_id');
    }

    public function sentShipments()
    {
        return $this->hasMany(Shipment::class, 'sender_id');
    }

    public function deliveredShipments()
    {
        return $this->hasMany(Shipment::class, 'traveler_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }
}
