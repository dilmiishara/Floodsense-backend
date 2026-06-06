<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorNode extends Model
{
    use HasFactory;

    protected $table = 'sensor_nodes';

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'status',
        'lora_dev_eui',
        'lora_app_eui',
        'last_seen',
        'gateway_id',
        'area_id'
    ];

    protected $casts = [
        'latitude'   => 'float',
        'longitude'  => 'float',
        'last_seen'  => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'gateway_id' => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────────────
    public function gateway()
    {
        return $this->belongsTo(Gateway::class, 'gateway_id');
    }

    // ── Scopes ──────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
}
