<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WaterLevelFetchService
{
    public function fetchAndStore(): int
    {
        try {
            $response = Http::get(env('WATER_LEVEL_API'));

            if (!$response->ok()) {
                Log::error('Water Level API failed: ' . $response->status());
                return 0;
            }

            $stations = $response->json();
            $now      = now();
            $rows     = [];

            foreach ($stations as $s) {
                // ── Only save Kalu Ganga stations ──
                if ($s['river_name'] !== 'Kalu Ganga') continue;

                $rows[] = [
                    'recorded_at'          => $now,
                    'station_name'         => $s['station_name'],
                    'river_name'           => $s['river_name'],
                    'water_level'          => $s['water_level'],
                    'previous_water_level' => $s['previous_water_level'],
                    'alert_status'         => $s['alert_status'],
                    'flood_score'          => $s['flood_score'],
                    'rising_or_falling'    => $s['rising_or_falling'],
                    'rainfall_mm'          => $s['rainfall_mm'],
                    'remarks'              => $s['remarks'],
                    'source_timestamp'     => $s['timestamp'],
                ];
            }

            DB::table('water_level_logs')->insert($rows);

            return count($rows);

        } catch (\Exception $e) {
            Log::error('Water level fetch error: ' . $e->getMessage());
            return 0;
        }
    }
}