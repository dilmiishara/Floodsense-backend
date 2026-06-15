<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class EmergencyController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'is_emergency' => 'required|boolean',
        ]);

        $isEmergency = $request->is_emergency;

        try {
            $result = $this->sendEmergencyNotification($isEmergency);

            return response()->json([
                'success'      => true,
                'message'      => $isEmergency
                    ? 'Emergency mode activated — all users notified'
                    : 'Emergency mode deactivated — all users notified',
                'is_emergency' => $isEmergency,
                'sent'         => $result['success'],
                'failed'       => $result['failed'],
            ]);
        } catch (\Exception $e) {
            Log::error('Emergency toggle error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function sendEmergencyNotification(bool $isEmergency): array
    {
        // Get all FCM tokens — both active and inactive
        // to try sending to all
        $tokens = DB::table('fcm_tokens')
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();

        Log::info('Emergency: found ' . count($tokens) . ' active tokens');

        if (empty($tokens)) {
            Log::info('No FCM tokens found for emergency notification');
            return ['success' => 0, 'failed' => 0];
        }

        // Set notification content
        if ($isEmergency) {
            $title  = '🚨 EMERGENCY FLOOD ALERT';
            $body   = 'Emergency flood situation declared in '
                . 'Rathnapura district. Evacuate immediately '
                . 'and move to nearest safe location!';
            $screen = 'safe-zones';
        } else {
            $title  = '✅ Emergency Alert Cleared';
            $body   = 'The flood emergency in Rathnapura district '
                . 'has been lifted. Stay cautious and '
                . 'monitor updates.';
            $screen = 'home';
        }

        // Initialize Firebase
        $factory   = (new Factory)->withServiceAccount(
            storage_path('app/firebase-credentials.json')
        );
        $messaging = $factory->createMessaging();

        $notification = Notification::create($title, $body);

        $successCount = 0;
        $failCount    = 0;

        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification($notification)
                    ->withData([
                        'screen'       => $screen,
                        'is_emergency' => $isEmergency ? 'true' : 'false',
                        'type'         => 'emergency',
                    ]);

                $messaging->send($message);
                $successCount++;
                Log::info('Emergency notification sent to token: '
                    . substr($token, 0, 20) . '...');

            } catch (\Exception $e) {
                $failCount++;
                Log::error('Failed emergency token: '
                    . substr($token, 0, 20)
                    . ' Error: ' . $e->getMessage());

                // Only deactivate if token is explicitly invalid
                if (str_contains($e->getMessage(), 'UNREGISTERED') ||
                    str_contains($e->getMessage(), 'INVALID_ARGUMENT')) {
                    DB::table('fcm_tokens')
                        ->where('token', $token)
                        ->update(['is_active' => false]);
                    Log::info('Deactivated invalid token');
                }
            }
        }

        Log::info("Emergency notification sent: "
            . "$successCount success, $failCount failed");

        return ['success' => $successCount, 'failed' => $failCount];
    }
}
