<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class SensorReadingController extends Controller
{
    public function store(Request $request)
    {
        // Better: move these to .env later
        $fixedSensorNodeId = 1;
        $fixedGatewayId    = 1;

        // Validation
        $validator = Validator::make($request->all(), [
            'water_level' => 'nullable|numeric',
            'rainfall'    => 'nullable|numeric',
            'humidity'    => 'nullable|numeric',
            'temperature' => 'nullable|numeric',
            'recorded_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // ✅ DIRECT INSERT INTO SUPABASE TABLE (FIXED)
            $insertedId = DB::table('sensor_readings')->insertGetId([
                'sensor_node_id' => $fixedSensorNodeId,
                'gateway_id'     => $fixedGatewayId,
                'water_level'    => $request->input('water_level'),
                'rainfall'       => $request->input('rainfall'),
                'humidity'       => $request->input('humidity'),
                'temperature'    => $request->input('temperature'),
                'recorded_at'    => $request->input('recorded_at', now()),
                'created_at'     => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sensor data saved successfully',
                'data' => [
                    'reading_id' => $insertedId
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error while saving sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
