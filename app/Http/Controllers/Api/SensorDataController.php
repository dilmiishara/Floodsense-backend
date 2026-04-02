<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorData;
use App\Models\Alert;
use App\Models\AlertThreshold;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use \App\Models\SensorReading;
class SensorDataController extends Controller
{
    /**
     * Store and process incoming sensor data
     */
  public function store(Request $request)
{
    
    $request->validate([
        'area_id'     => 'required|exists:areas,id',
        'sensor_id'   => 'required|string',
        'water_level' => 'nullable|numeric',
        'rainfall'    => 'nullable|numeric',
    ]);

    try {
       
        $sensorEntry = SensorReading::create([
            'sensor_id'   => $request->sensor_id,
            'area_id'     => $request->area_id,
            'water_level' => $request->water_level,
            'rainfall'    => $request->rainfall,
            'humidity'    => $request->humidity,
            'battery_level' => $request->battery_level ?? 100,
        ])->load('area');

        
        $threshold = AlertThreshold::where('area_id', $sensorEntry->area_id)->first();

        if ($threshold) {
            $severity = null;
            $alertType = "";
            $message = "";

            // --- WATER LEVEL CHECK ---
            if ($request->water_level >= $threshold->water_critical_level) {
                $severity = 'CRITICAL';
                $alertType = 'Flood';
                $message = "Critical flood level reached: {$request->water_level}m in {$sensorEntry->area->name}.";
            } elseif ($request->water_level >= $threshold->water_warning_level) {
                $severity = 'HIGH';
                $alertType = 'Flood';
                $message = "Warning: Water level rising in {$sensorEntry->area->name} ({$request->water_level}m).";
            }

            // --- RAINFALL CHECK ---
            if ($request->rainfall >= $threshold->rain_critical_level) {
                $severity = 'CRITICAL';
                $alertType = 'Rainfall';
                $message = "Extreme rainfall detected in {$sensorEntry->area->name}: {$request->rainfall}mm.";
            } elseif ($request->rainfall >= $threshold->rain_warning_level) {
                $severity = 'HIGH';
                $alertType = 'Rainfall';
                $message = "Heavy rain recorded in {$sensorEntry->area->name} ({$request->rainfall}mm).";
            }

           
            if ($severity) {
                Alert::create([
                    'area_id'     => $sensorEntry->area_id,
                    'type'        => $alertType,
                    'location'    => $sensorEntry->area->name,
                    'severity'    => $severity,
                    'message'     => $message,
                    'status'      => 'active',
                    'detected_at' => now(),
                ]);
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Processed'], 201);

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
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