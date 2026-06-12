<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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



    // Active/Upcoming alerts — forecast_time is in the future
    public function activeAlertsMobile()
    {
        try {
            $predictions = Prediction::whereIn('flood_risk_level', [
                'Alert', 'Minor Flood', 'Major Flood'
            ])
                ->where('forecast_time', '>=', now())
                ->orderBy('created_at', 'desc')  // soonest forecast first
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

// History — forecast_time is in the past
    public function historyAlertsMobile()
    {
        try {
            $predictions = Prediction::whereIn('flood_risk_level', [
                'Alert', 'Minor Flood', 'Major Flood'
            ])
                ->where('forecast_time', '<', now())
                ->orderBy('forecast_time', 'desc')
                ->limit(50)
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
