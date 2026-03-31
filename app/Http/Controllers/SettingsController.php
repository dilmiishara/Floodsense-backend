<?php

namespace App\Http\Controllers;

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

        $booleanFields = ['emergency_mode', 'maintenance_mode'];

        $data = array_map(function ($val) use (&$data) {
            return $val;
        }, $data);

        // ✅ Properly cast known boolean fields
        foreach ($booleanFields as $field) {
            if (array_key_exists($field, $data)) {
                $raw = $data[$field];
                // Handles: "1", "0", "true", "false", 1, 0, true, false, ""
                $data[$field] = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            }
        }

        return response()->json($data);
    }

    // POST /api/settings/{section}
    public function update(Request $request, string $section): JsonResponse
    {
        $allowed = ['system', 'sensor', 'alerts', 'map', 'safezone'];

        if (!in_array($section, $allowed)) {
            return response()->json(['error' => 'Invalid section'], 422);
        }

        $booleanFields = ['emergency_mode', 'maintenance_mode'];

        $payload = $request->all();

        // ✅ Convert booleans to "true"/"false" strings before saving
        foreach ($booleanFields as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = filter_var($payload[$field], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
            }
        }

        Setting::saveSection($section, $payload);

        return response()->json(['success' => true, 'message' => 'Settings saved.']);
    }
}