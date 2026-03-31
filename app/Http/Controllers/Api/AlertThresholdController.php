<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AlertThreshold;
use Illuminate\Http\Request;

class AlertThresholdController extends Controller
{
    public function index() {
        
        return response()->json(AlertThreshold::with('area')->get());
    }

    public function store(Request $request) {
        
        $threshold = AlertThreshold::updateOrCreate(
            ['area_id' => $request->area_id], 
            [
                'water_warning_level'  => $request->water_warning_level,
                'water_critical_level' => $request->water_critical_level,
                'rain_warning_level'   => $request->rain_warning_level,
                'rain_critical_level'  => $request->rain_critical_level,
                'rise_rate_limit'      => $request->rise_rate_limit ?? 0.35
            ]
        );

        return response()->json([
            'message' => 'Threshold saved successfully!', 
            'data' => $threshold
        ]);
    }
}