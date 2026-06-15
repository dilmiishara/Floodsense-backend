<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterLevelLog extends Model
{
    protected $table = 'water_level_logs';

    protected $fillable = [
        'recorded_at',
        'station_name',
        'river_name',
        'water_level',
        'previous_water_level',
        'alert_status',
        'flood_score',
        'rising_or_falling',
        'rainfall_mm',
        'remarks',
        'source_timestamp',
    ];

    public $timestamps = false;
}
