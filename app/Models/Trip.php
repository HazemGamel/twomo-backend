<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'departure_date' => 'datetime',
        'available_seats' => 'integer',
        'total_seats' => 'integer',
        'price_per_seat' => 'double',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function bookings()
    {
        return $this->hasMany(TripBooking::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
