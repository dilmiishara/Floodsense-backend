<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WaterLevelLog;

class WaterLevelController extends Controller
{
    // Latest reading per station
    public function latestPerStation()
    {
        try {
            $latest = DB::table('water_level_logs')
                ->whereIn('id', function ($query) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('water_level_logs')
                        ->whereIn('station_name', [
                            'Ellagawa', 'Putupaula', 'Rathnapura'
                        ])
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
                'data'    => $latest,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
