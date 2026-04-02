<?php

namespace App\Models;

// app/Models/Setting.php

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table      = 'settings';
    protected $fillable   = ['section', 'key_name', 'value'];
    public    $timestamps = false;  // we handle updated_at in Supabase

    // Helper: get all settings for a section as key=>value array
    public static function getSection(string $section): array
    {
        return static::where('section', $section)
            ->pluck('value', 'key_name')
            ->toArray();
    }

    // Helper: upsert an array of key=>value pairs for a section
    public static function saveSection(string $section, array $data): void
    {
        foreach ($data as $key => $value) {
            static::updateOrCreate(
                ['section' => $section, 'key_name' => $key],
                ['value'   => (string) $value]
            );
        }
    }
}