<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    /**
     * Return only predictions with a non-Normal flood_risk_level,
     * i.e. Alert, Minor, Major — the latest entry per station.
     */
    public function getAlertPredictions()
    {
        $predictions = Prediction::whereIn('flood_risk_level', ['Alert', 'Minor', 'Major'])
            ->orderBy('forecast_time', 'desc')
            ->get();

        return response()->json($predictions);
    }

    /**
     * Return ALL latest predictions (for a full forecast view if needed).
     */
    public function index()
    {
        $predictions = Prediction::orderBy('forecast_time', 'desc')
            ->limit(30)
            ->get();

        return response()->json($predictions);
    }


    // Get latest prediction for each station
    public function latest()
    {
        try {
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
