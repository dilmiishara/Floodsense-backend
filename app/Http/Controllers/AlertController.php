<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    // 1. දැනට පවතින Active Alerts ලබා ගැනීම (Tab 1 සඳහා)
    public function getActiveAlerts()
    {
        $alerts = Alert::where('status', 'active')
                       ->orderBy('detected_at', 'desc')
                       ->get();
        return response()->json($alerts);
    }

    // 2. Alert History ලබා ගැනීම (Tab 2 සඳහා)
    public function getAlertHistory()
    {
        $history = Alert::where('status', 'resolved')
                        ->orderBy('detected_at', 'desc')
                        ->get();
        return response()->json($history);
    }

    // 3. අලුතින් Alert එකක් පද්ධතියට එකතු කිරීම (Simulated/Sensor Data)
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'location' => 'required',
            'severity' => 'required',
            'message' => 'required'
        ]);

        $alert = Alert::create([
            'type' => $request->type,
            'location' => $request->location,
            'severity' => $request->severity,
            'message' => $request->message,
            'status' => 'active',
            'detected_at' => now()
        ]);

        return response()->json(['message' => 'Alert generated successfully', 'data' => $alert], 201);
    }

    public function resolve($id)
{
    $alert = Alert::find($id);

    if (!$alert) {
        return response()->json(['message' => 'Alert not found'], 404);
    }

    // Status එක 'resolved' කර Update කරනවා
    $alert->update([
        'status' => 'resolved'
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Alert resolved and moved to history.'
    ]);
}
}