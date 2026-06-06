<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    public $timestamps = false; 
    
    protected $table = 'sensor_readings'; 

    protected $fillable = [
        'sensor_node_id',  
        'gateway_id',      
        'water_level', 
        'rainfall', 
        'humidity', 
        'temperature'      
    ];

    
    public function sensorNode()
    {
        return $this->belongsTo(SensorNode::class, 'sensor_node_id');
    }
}