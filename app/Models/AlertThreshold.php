<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertThreshold extends Model
{
    protected $fillable = [
        'area_id', 
        'water_warning_level', 'water_critical_level',
        'rain_warning_level', 'rain_critical_level',
        'rise_rate_limit'
    ];

    public function area() {
        return $this->belongsTo(Area::class);
    }
}