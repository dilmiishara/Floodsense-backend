<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    use HasFactory;

    protected $table = 'sensor_data';
    public $timestamps = false; 

    protected $fillable = [
        'device_id',
        'area_id',
        'water_level',
        'rainfall',
        'humidity',
        'battery_level',
        'latitude',
        'longitude',
        'recorded_at'
    ];

   
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
}