<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
   
    public function getActiveAlerts()
    {
        $alerts = Alert::where('status', 'active')
                       ->orderBy('detected_at', 'desc')
                       ->get();
        return response()->json($alerts);
    }

   
    public function getAlertHistory()
    {
        $history = Alert::where('status', 'resolved')
                        ->orderBy('detected_at', 'desc')
                        ->get();
        return response()->json($history);
    }

   
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

    
    $alert->update([
        'status' => 'resolved'
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Alert resolved and moved to history.'
    ]);
}


}