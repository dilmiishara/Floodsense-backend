<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SafeLocation extends Model
{
    use HasFactory;

    protected $table = 'safe_locations'; // table name in lowercase

    protected $fillable = [
        'location_name',
        'location_type',
        'address',
        'district',
        'province',
        'latitude',
        'longitude',
        'max_capacity',
        'contact_person',
        'contact_number',
        'disabled_access',
        'created_by',
    ];
}
