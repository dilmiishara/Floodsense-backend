<?php

// app/Models/Area.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Area extends Model
{
    use HasFactory;

    // Supabase table name
    protected $table = 'areas';

    // We removed timestamps in the SQL earlier, so tell Laravel not to look for them
    public $timestamps = false;

    protected $fillable = ['name'];

    // Relationship: One area has many users
    public function users()
    {
        return $this->hasMany(User::class, 'area_id');
    }
}