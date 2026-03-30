<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// app/Models/User.php

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',      // This links to your roles table
        'area_id',   // This links to your areas table
        'telephone',
        'status', // Add this since we discussed it earlier
    ];

    // Add this relationship method
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    // Also add this to link to your roles table properly
    public function role_data()
    {
        return $this->belongsTo(Role::class, 'role');
    }
}