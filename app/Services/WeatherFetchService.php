<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WeatherFetchService
{
    public function fetchAndStore(): bool
    {
        try {
            $response = Http::get(env('WEATHER_API_URL') . '/forecast.json', [
                'key'    => env('WEATHER_API_KEY'),
                'q'      => env('WEATHER_COORDS'),
                'days'   => 3,
                'aqi'    => 'no',
                'alerts' => 'no',
            ]);

            if (!$response->ok()) {
                Log::error('WeatherAPI failed: ' . $response->status());
                return false;
            }

            $data    = $response->json();
            $current = $data['current'];

            DB::table('weather_logs')->insert([
                'recorded_at'   => now(),
                'wind_kph'      => $current['wind_kph'],
                'wind_dir'      => $current['wind_dir'],
                'humidity'      => $current['humidity'],
                'pressure_mb'   => $current['pressure_mb'],
                'precip_mm'     => $current['precip_mm'],
                'cloud'         => $current['cloud'],
                'uv'            => $current['uv'],
                'vis_km'        => $current['vis_km'],
                'temp_c'        => $current['temp_c'],
                'feelslike_c'   => $current['feelslike_c'],
                'forecast_json' => json_encode($data['forecast']['forecastday']),
                'location'      => 'Ratnapura',
                'source'        => 'weatherapi',
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Weather fetch error: ' . $e->getMessage());
            return false;
        }
    }
}