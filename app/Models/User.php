<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name', 
        'last_name',
        'email',
        'password',
        'role',      
        'area_id',   
        'telephone',
        'status',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function role_data()
    {
        return $this->belongsTo(Role::class, 'role');
    }
}