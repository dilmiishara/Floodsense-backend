<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PredictionController extends Controller
{
    public function latest()
    {
        try {
            // Get latest prediction for each station
            $predictions = DB::table('predictions')
                ->select(
                    'station_name',
                    'forecast_time',
                    'predicted_water_level',
                    'flood_risk_level',
                    'affected_area_sqkm',
                    'duration_days',
                    'temperature',
                    'humidity',
                    'rainfall',
                    'created_at'
                )
                ->whereIn('id', function ($query) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('predictions')
                        ->groupBy('station_name');
                })
                ->orderByRaw("CASE
                    WHEN station_name = 'Ellagawa'   THEN 1
                    WHEN station_name = 'Putupaula'  THEN 2
                    WHEN station_name = 'Rathnapura' THEN 3
                    END")
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $predictions,
                'count'   => $predictions->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
