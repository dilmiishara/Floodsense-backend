<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'name', 'type', 'format', 'area_id', 'file_path', 'file_size'
    ];

    
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
}