<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    protected $table = 'predictions';

    protected $fillable = [
        'station_name',
        'forecast_time',
        'predicted_water_level',
        'flood_risk_level',
        'affected_area_sqkm',
        'duration_days',
        'temperature',
        'humidity',
        'rainfall',
    ];
}