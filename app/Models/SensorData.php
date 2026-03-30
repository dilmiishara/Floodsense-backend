<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    use HasFactory;

    protected $table = 'sensor_data';
    public $timestamps = false; // අපි manually created_at පාවිච්චි කරන නිසා

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

    // Sensor එක අයත් ප්‍රදේශය ලබා ගැනීමට
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
}