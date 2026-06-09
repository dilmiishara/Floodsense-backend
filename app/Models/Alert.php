<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
    'type',  'severity', 'message', 'status', 'detected_at', 'area_id' 
];

    public function area()
{
    
    return $this->belongsTo(Area::class, 'area_id');
}


public function threshold()
{
    return $this->hasOne(AlertThreshold::class, 'area_id', 'area_id');
}


public function sensorReading()
{
    return $this->belongsTo(SensorReading::class, 'sensor_reading_id');
}
}