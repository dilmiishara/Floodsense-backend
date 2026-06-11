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
}
