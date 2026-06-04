<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    use HasFactory;

    protected $table = 'gateways';

    protected $fillable = [
        'name',
        'lora_gateway_eui',
        'latitude',
        'longitude',
        'status',
        'last_seen',
        'location_name',
    ];

    protected $casts = [
        'latitude'   => 'float',
        'longitude'  => 'float',
        'last_seen'  => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}
