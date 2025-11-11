<?php
// app/Models/DriverLocation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id', 'latitude', 'longitude', 'location_updated_at'
    ];

    protected $casts = [
        'location_updated_at' => 'datetime'
    ];

    // Relationships
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}