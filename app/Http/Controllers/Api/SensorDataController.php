<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorData;
use App\Models\Alert;
use App\Models\AlertThreshold;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SensorDataController extends Controller
{
    /**
     * Store and process incoming sensor data
     */
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'area_id'     => 'required|exists:areas,id',
            'water_level' => 'required|numeric',
            'rainfall'    => 'required|numeric',
            'device_id'   => 'nullable|string'
        ]);

        try {
            // 2. Save the Raw Sensor Entry and load the Area relationship
            $sensorEntry = SensorData::create([
                'device_id'   => $request->device_id ?? 'SNSR-MAIN',
                'area_id'     => $request->area_id,
                'water_level' => $request->water_level,
                'rainfall'    => $request->rainfall,
                'recorded_at' => now(),
            ])->load('area');

            // 3. Fetch Thresholds for this specific Area
            $threshold = AlertThreshold::where('area_id', $sensorEntry->area_id)->first();

            // 4. Automated Alert Logic
            if ($threshold) {
                $this->checkAndTriggerAlerts($sensorEntry, $threshold);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Sensor data processed successfully.'
            ], 201);

        } catch (\Exception $e) {
            Log::error("Sensor Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Compare data against thresholds
     */
    private function checkAndTriggerAlerts($data, $threshold)
    {
        // Get Area Name from the relationship
        $areaName = $data->area->name ?? 'Unknown Location';

        // --- CHECK 1: WATER LEVEL ---
        if ($data->water_level >= $threshold->water_critical_level) {
            $this->createAlert($data->area_id, $areaName, 'Flood', 'CRITICAL', "Critical flood level reached: {$data->water_level}m in {$areaName}.");
        } 
        elseif ($data->water_level >= $threshold->water_warning_level) {
            $this->createAlert($data->area_id, $areaName, 'Flood', 'HIGH', "Warning: Water level rising in {$areaName} ({$data->water_level}m).");
        }

        // --- CHECK 2: RAINFALL ---
        if ($data->rainfall >= $threshold->rain_critical_level) {
            $this->createAlert($data->area_id, $areaName, 'Rainfall', 'HIGH', "Extreme rainfall detected in {$areaName}: {$data->rainfall}mm.");
        }
        elseif ($data->rainfall >= $threshold->rain_warning_level) {
             $this->createAlert($data->area_id, $areaName, 'Rainfall', 'MEDIUM', "Heavy rain recorded in {$areaName} ({$data->rainfall}mm).");
        }
    }

    /**
     * Create a new alert record
     */
    private function createAlert($areaId, $location, $type, $severity, $message)
    {
        Alert::create([
            'area_id'     => $areaId, 
            'type'        => $type,
            'location'    => $location,
            'severity'    => $severity,
            'message'     => $message,
            'status'      => 'active',
            'detected_at' => now(),
        ]);
    }
}