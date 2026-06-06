<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
    'type', 'location', 'severity', 'message', 'status', 'detected_at', 'area_id' // 👈 'area_id' මෙහි තිබිය යුතුයි
];

    public function area()
{
    // Alert එකක් අයිති වන්නේ එක් Area එකකටයි
    return $this->belongsTo(Area::class, 'area_id');
}

// Alert එකට අදාළ Threshold අගයන් ලබා ගැනීමට
public function threshold()
{
    return $this->hasOne(AlertThreshold::class, 'area_id', 'area_id');
}

// Alert එක සිදු වූ මොහොතේ Sensor Readings ලබා ගැනීමට
public function sensorReading()
{
    // මෙය සරල සම්බන්ධයක් ලෙස තබන්න. 
    // Alert එකට අදාළ ප්‍රදේශයේ අවසන් රීඩින් එක ලබා ගැනීමට මෙය සෑහේ.
    return $this->hasOne(SensorReading::class, 'area_id', 'area_id')->latestOfMany();
}
}