<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'reward_fee' => 'double',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function traveler()
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }
}
