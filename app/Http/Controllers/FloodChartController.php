<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class FloodChartController extends Controller
{
    public function history($station)
    {
        $rows = DB::table('water_level_logs')
            ->where('station_name', $station)
            ->orderBy('recorded_at', 'desc')
            ->limit(13)
            ->get()
            ->reverse()
            ->values();

        if ($rows->isEmpty()) {
            return response()->json(['past' => [], 'current' => null]);
        }

        $current = $rows->last();
        $past    = $rows->slice(0, $rows->count() - 1)->values();

        return response()->json([
        'past'    => $past->map(fn($r) => [
                'recorded_at'  => $r->recorded_at,
                'water_level'  => (float) $r->water_level,
                'alert_status' => $r->alert_status,
            ]),
            'current' => [
                'recorded_at'  => $current->recorded_at,
                'water_level'  => (float) $current->water_level,
                'alert_status' => $current->alert_status,
            ],
        ]);
    }

    public function predictions($station)
    {
        $latestBatch = DB::table('predictions')
            ->where('station_name', $station)
            ->max('created_at');

        if (!$latestBatch) {
            return response()->json(['predictions' => []]);
        }

        $rows = DB::table('predictions')
            ->where('station_name', $station)
            ->where('created_at', $latestBatch)
            ->orderBy('forecast_time', 'asc')
            ->limit(12)
            ->get();

        return response()->json([
            'predictions' => $rows->map(fn($r) => [
                'forcast_time'          => $r->forecast_time,
                'predicted_water_level' => (float) $r->predicted_water_level,
                'flood_risk_level'      => $r->flood_risk_level,
            ]),
        ]);
    }

    public function floodPredictions($station)
{
    // Get the latest batch (max created_at) for this station
    $latestBatch = DB::table('predictions')
        ->where('station_name', $station)
        ->max('created_at');

    if (!$latestBatch) {
        return response()->json([]);
    }

    // Get all rows from that batch ordered by forecast_time
    $rows = DB::table('predictions')
        ->where('station_name', $station)
        ->where('created_at', $latestBatch)
        ->orderBy('forecast_time', 'asc')
        ->get();

    return response()->json($rows->map(fn($r) => [
        'station_name'           => $r->station_name,
        'forecast_time'          => $r->forecast_time,
        'predicted_water_level'  => (float) $r->predicted_water_level,
        'flood_risk_level'       => $r->flood_risk_level,
        'rainfall'               => (float) $r->rainfall,
        'temperature'            => (float) $r->temperature,
        'created_at'             => $r->created_at,
    ]));
}
}