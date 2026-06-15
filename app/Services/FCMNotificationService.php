<?php

namespace App\Services;

use App\Models\FcmToken;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FCMNotificationService
{
    private $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(
            storage_path('app/firebase-credentials.json')
        );
        $this->messaging = $factory->createMessaging();
    }

    public function sendToAll(
        string $title,
        string $body,
        array $data = []
    ): void {
        // Get all active tokens
        $tokens = FcmToken::where('is_active', true)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            Log::info('No FCM tokens found');
            return;
        }

        $notification = Notification::create($title, $body);

        // Send in batches of 500
        $chunks = array_chunk($tokens, 500);

        foreach ($chunks as $chunk) {
            $this->sendBatch($chunk, $notification, $data);
        }
    }

    private function sendBatch(
        array $tokens,
        Notification $notification,
        array $data = []
    ): void {
        try {
            $messages = [];
            foreach ($tokens as $token) {
                $messages[] = CloudMessage::withTarget('token', $token)
                    ->withNotification($notification)
                    ->withData($data);
            }

            $report = $this->messaging->sendAll($messages);

            Log::info('FCM sent: ' . $report->successes()->count() .
                ' success, ' . $report->failures()->count() . ' failed');

            // Remove invalid tokens
            foreach ($report->failures()->getItems() as $failure) {
                $invalidToken = $failure->target()->value();
                FcmToken::where('token', $invalidToken)
                    ->update(['is_active' => false]);
                Log::info('Deactivated invalid token: ' . $invalidToken);
            }
        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage());
        }
    }

    public function sendToDevice(
        string $token,
        string $title,
        string $body,
        array $data = []
    ): void {
        try {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $this->messaging->send($message);
            Log::info('FCM sent to device: ' . $token);
        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage());
        }
    }
}
