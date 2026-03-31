<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{

public $timestamps = false;
    
    protected $fillable = [
        'sensor_id', 
        'area_id', 
        'water_level', 
        'rainfall', 
        'humidity', 
        'battery_level',
        'latitude',
        'longitude'
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
}