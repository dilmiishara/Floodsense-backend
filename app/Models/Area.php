<?php

// app/Models/Area.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Area extends Model
{
    use HasFactory;

    protected $table = 'areas';

    
    public $timestamps = false;

    protected $fillable = ['name'];

    
    public function users()
    {
        return $this->hasMany(User::class, 'area_id');
    }

    public function reports()
{
    return $this->hasMany(Report::class);
}

public function sensorNodes()
    {
        return $this->hasMany(SensorNode::class, 'area_id');
    }
}