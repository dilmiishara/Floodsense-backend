<?php

namespace App\Http\Controllers;

// app/Http/Controllers/SettingsController.php

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    // GET /api/settings/{section}
    public function show(string $section): JsonResponse
    {
        $allowed = ['system', 'sensor', 'alerts', 'map', 'safezone'];

        if (!in_array($section, $allowed)) {
            return response()->json(['error' => 'Invalid section'], 422);
        }

        $data = Setting::getSection($section);

        // Cast boolean strings back to actual booleans for the frontend
        $data = array_map(function ($val) {
            if ($val === 'true')  return true;
            if ($val === 'false') return false;
            return $val;
        }, $data);

        return response()->json($data);
    }

    // POST /api/settings/{section}
    public function update(Request $request, string $section): JsonResponse
    {
        $allowed = ['system', 'sensor', 'alerts', 'map', 'safezone'];

        if (!in_array($section, $allowed)) {
            return response()->json(['error' => 'Invalid section'], 422);
        }

        Setting::saveSection($section, $request->all());

        return response()->json(['success' => true, 'message' => 'Settings saved.']);
    }
}