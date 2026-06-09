<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;
use App\Services\FCMNotificationService;

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

// Add this as a separate method in AlertController class
    public function sendAlertNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'type' => 'nullable|string',
            'severity' => 'nullable|string',
        ]);

        try {
            $fcmService = new FCMNotificationService();
            $fcmService->sendToAll(
                title: '⚠️ ' . $request->title,
                body: $request->message,
                data: [
                    'type' => $request->type ?? 'Alert',
                    'severity' => $request->severity ?? 'HIGH',
                ]
            );

            return response()->json([
                'message' => 'Notification sent successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
