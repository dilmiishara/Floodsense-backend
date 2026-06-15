<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FloodNotificationService
{
    public function checkAndNotify()
    {
        try {
            // Station coordinates
            $stationCoords = [
                'Ellagawa'   => ['lat' => 6.730, 'lng' => 80.213],
                'Putupaula'  => ['lat' => 6.612, 'lng' => 80.060],
                'Rathnapura' => ['lat' => 6.690, 'lng' => 80.380],
            ];

            // Get latest prediction per station
            $latestIds = DB::table('predictions')
                ->select(DB::raw('MAX(id) as id'))
                ->groupBy('station_name')
                ->pluck('id');

            // Get new flood alerts not yet notified
            $newAlerts = DB::table('predictions')
                ->whereIn('id', $latestIds)
                ->whereIn('flood_risk_level', [
                    'Alert', 'Minor Flood', 'Major Flood'
                ])
                ->whereNotIn('id', function ($query) {
                    $query->select('prediction_id')
                        ->from('notification_logs');
                })
                ->get();

            if ($newAlerts->isEmpty()) {
                Log::info('No new flood alerts to notify');
                return;
            }

            // Get all active FCM tokens with locations
            // Use DISTINCT to avoid duplicate tokens
            $tokensWithLocation = DB::table('user_locations')
                ->join('fcm_tokens', 'user_locations.fcm_token', '=', 'fcm_tokens.token')
                ->where('fcm_tokens.is_active', true)
                ->select(
                    DB::raw('DISTINCT ON (fcm_tokens.token) fcm_tokens.token'),
                    'user_locations.latitude',
                    'user_locations.longitude'
                )
                ->get();

            // Get ALL active tokens
            $allActiveTokens = DB::table('fcm_tokens')
                ->where('is_active', true)
                ->pluck('token')
                ->toArray();

            // Tokens with location
            $tokensWithLocationArray = $tokensWithLocation
                ->pluck('token')
                ->toArray();

            // Tokens WITHOUT location → notify anyway (Option A)
            $tokensWithoutLocation = array_values(array_diff(
                $allActiveTokens,
                $tokensWithLocationArray
            ));

            foreach ($newAlerts as $alert) {
                $coords = $stationCoords[$alert->station_name] ?? null;

                // Get affected radius in km
                $radiusKm = $this->getRadiusKm(
                    (float) $alert->affected_area_sqkm
                );

                Log::info("Checking alert for {$alert->station_name} — radius: {$radiusKm}km");

                // Find tokens inside affected area
                $tokensToNotify = [];

                if ($coords) {
                    foreach ($tokensWithLocation as $userToken) {
                        $distance = $this->haversineDistance(
                            $userToken->latitude,
                            $userToken->longitude,
                            $coords['lat'],
                            $coords['lng']
                        );

                        if ($distance <= $radiusKm) {
                            // Only add if not already in list
                            if (!in_array($userToken->token, $tokensToNotify)) {
                                $tokensToNotify[] = $userToken->token;
                                Log::info("User within {$distance}km of {$alert->station_name} — will notify");
                            }
                        }
                    }
                }

                // Add tokens without location
                foreach ($tokensWithoutLocation as $token) {
                    if (!in_array($token, $tokensToNotify)) {
                        $tokensToNotify[] = $token;
                    }
                }

                Log::info("Total tokens to notify for {$alert->station_name}: " . count($tokensToNotify));

                if (!empty($tokensToNotify)) {
                    $this->sendFloodNotification($alert, $tokensToNotify);
                }

                // Log notification
                DB::table('notification_logs')->insert([
                    'prediction_id'    => $alert->id,
                    'station_name'     => $alert->station_name,
                    'flood_risk_level' => $alert->flood_risk_level,
                    'notified_at'      => now(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Flood notification error: ' . $e->getMessage());
        }
    }

    // Calculate radius from affected area sq km
    private function getRadiusKm(float $areaSqKm): float
    {
        if ($areaSqKm <= 0) return 10.0; // default 10km
        return sqrt($areaSqKm / M_PI);
    }

    // Haversine distance formula
    private function haversineDistance(
        float $lat1, float $lng1,
        float $lat2, float $lng2
    ): float {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function sendFloodNotification($alert, array $tokens)
    {
        try {
            $title = $this->getTitle($alert->flood_risk_level);
            $body  = $this->getMessage($alert);

            if (empty($tokens)) {
                Log::info('No tokens to notify');
                return;
            }

            $factory = (new Factory)->withServiceAccount(
                storage_path('app/firebase-credentials.json')
            );
            $messaging    = $factory->createMessaging();
            $notification = Notification::create($title, $body);

            $successCount = 0;
            $failCount    = 0;

            foreach ($tokens as $token) {
                try {
                    $message = CloudMessage::withTarget('token', $token)
                        ->withNotification($notification)
                        ->withData([
                            'station_name'     => $alert->station_name,
                            'flood_risk_level' => $alert->flood_risk_level,
                            'screen'           => 'alerts',
                            'type'             => 'flood',
                        ]);

                    $messaging->send($message);
                    $successCount++;

                } catch (\Exception $e) {
                    $failCount++;
                    Log::error('Failed flood token: '
                        . substr($token, 0, 20)
                        . ' Error: ' . $e->getMessage());

                    // Only deactivate if token is explicitly invalid
                    if (str_contains($e->getMessage(), 'UNREGISTERED') ||
                        str_contains($e->getMessage(), 'Requested entity was not found')) {
                        DB::table('fcm_tokens')
                            ->where('token', $token)
                            ->update(['is_active' => false]);
                        Log::info('Deactivated invalid token: ' . substr($token, 0, 20));
                    }
                }
            }

            Log::info("Notification sent to {$alert->station_name}: $successCount success, $failCount failed");

        } catch (\Exception $e) {
            Log::error('Send notification error: ' . $e->getMessage());
        }
    }

    private function getTitle(string $riskLevel): string
    {
        switch ($riskLevel) {
            case 'Major Flood':
                return '🔴 MAJOR FLOOD WARNING';
            case 'Minor Flood':
                return '🟠 Minor Flood Alert';
            case 'Alert':
                return '🟡 Flood Alert';
            default:
                return '⚠️ Flood Warning';
        }
    }

    private function getMessage($alert): string
    {
        switch ($alert->flood_risk_level) {
            case 'Major Flood':
                return "Major flood detected at {$alert->station_name}. Water level: {$alert->predicted_water_level}m. Take immediate action!";
            case 'Minor Flood':
                return "Minor flood at {$alert->station_name}. Water level: {$alert->predicted_water_level}m. Stay cautious.";
            case 'Alert':
                return "Flood alert at {$alert->station_name}. Water level: {$alert->predicted_water_level}m. Monitor closely.";
            default:
                return "Flood warning at {$alert->station_name}.";
        }
    }
}
