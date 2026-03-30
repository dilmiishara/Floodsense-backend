<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AlertThreshold;
use Illuminate\Http\Request;

class AlertThresholdController extends Controller
{
    // සියලුම Thresholds ලබා ගැනීම (Frontend Table එකට)
    public function index() {
        return AlertThreshold::with('area')->get();
    }

    // අලුතින් එකතු කිරීම හෝ Edit කිරීම (UpdateOrCreate)
    public function store(Request $request) {
        $threshold = AlertThreshold::updateOrCreate(
            ['area_id' => $request->area_id], // Area එක දැනටමත් ඇත්නම් Edit කරයි
            [
                'water_warning_level' => $request->water_warning,
                'water_critical_level' => $request->water_critical,
                'rain_warning_level' => $request->rain_warning,
                'rain_critical_level' => $request->rain_critical,
                'rise_rate_limit'    => $request->rise_rate
            ]
        );

        return response()->json(['message' => 'Threshold saved successfully!', 'data' => $threshold]);
    }
}