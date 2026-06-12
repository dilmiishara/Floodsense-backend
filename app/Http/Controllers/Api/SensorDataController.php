<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\AlertThreshold;
use App\Models\SensorNode;
use App\Models\SensorReading;
use Illuminate\Http\Request;

class SensorDataController extends Controller
{
    /**
     * GET /api/sensor-readings/latest
     * Returns the most recent sensor reading (for the IoT Device card)
     */
    public function getLatest()
    {
        $reading = SensorReading::with('sensorNode')
            ->latest('recorded_at')
            ->first();

        if (!$reading) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No sensor readings found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'deviceId'       => 'IOT-DEVICE-' . str_pad($reading->sensor_node_id, 3, '0', STR_PAD_LEFT),
                'waterLevel'     => (float) $reading->water_level,
                'rainfall'       => (float) $reading->rainfall,
                'humidity'       => (float) $reading->humidity,
                'temperature'    => (float) $reading->temperature,
                'signalStrength' => 94,   // static — update if you have a signal field
                'updatedAt'      => \Carbon\Carbon::parse($reading->recorded_at)->format('h:i A'),
                'recordedAt'     => $reading->recorded_at,
            ],
        ]);
    }

    /**
     * POST /api/sensor-data
     * Stores a new sensor reading and evaluates alert thresholds
     */
    public function store(Request $request)
    {
        $request->validate([
            'sensor_node_id' => 'required|integer|exists:sensor_nodes,id',
            'gateway_id'     => 'required|integer',
            'water_level'    => 'nullable|numeric',
            'rainfall'       => 'nullable|numeric',
            'humidity'       => 'nullable|numeric',
            'temperature'    => 'nullable|numeric',
        ]);

        try {
            $reading = SensorReading::create([
                'sensor_node_id' => $request->sensor_node_id,
                'gateway_id'     => $request->gateway_id,
                'water_level'    => $request->water_level,
                'rainfall'       => $request->rainfall,
                'humidity'       => $request->humidity,
                'temperature'    => $request->temperature,
            ]);

            $sensorNode = SensorNode::with('area')->find($request->sensor_node_id);

            if ($sensorNode && $sensorNode->area) {
                $areaId    = $sensorNode->area_id;
                $areaName  = $sensorNode->area->name;
                $threshold = AlertThreshold::where('area_id', $areaId)->first();

                if ($threshold) {
                    $severity  = null;
                    $alertType = '';
                    $message   = '';

                    // Water level check
                    if ($request->water_level >= $threshold->water_critical_level) {
                        $severity  = 'CRITICAL';
                        $alertType = 'Flood';
                        $message   = "Critical flood level reached: {$request->water_level}m in {$areaName}.";
                    } elseif ($request->water_level >= $threshold->water_warning_level) {
                        $severity  = 'HIGH';
                        $alertType = 'Flood';
                        $message   = "Warning: Water level rising in {$areaName} ({$request->water_level}m).";
                    }

                    // Rainfall check
                    if ($request->rainfall >= $threshold->rain_critical_level) {
                        $severity  = 'CRITICAL';
                        $alertType = 'Rainfall';
                        $message   = "Extreme rainfall detected in {$areaName}: {$request->rainfall}mm.";
                    } elseif ($request->rainfall >= $threshold->rain_warning_level) {
                        $severity  = 'HIGH';
                        $alertType = 'Rainfall';
                        $message   = "Heavy rain recorded in {$areaName} ({$request->rainfall}mm).";
                    }

                    if ($severity) {
                        Alert::create([
                            'area_id'     => $areaId,
                            'type'        => $alertType,
                            'severity'    => $severity,
                            'message'     => $message,
                            'status'      => 'active',
                            'detected_at' => now(),
                        ]);
                    }
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Sensor reading recorded and threshold evaluated successfully.',
                'data'    => $reading,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Process failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}