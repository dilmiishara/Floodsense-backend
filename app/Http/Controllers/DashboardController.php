<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\SafeLocation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get aggregated metrics and logs specifically for the core dashboard screen.
     */
    public function getMasterDashboardData()
    {
        // ALERTS DATA MATRIX
        $criticalCount = Alert::where('status', 'active')
                              ->whereIn('severity', ['critical', 'CRITICAL'])
                              ->count();

        $recentAlerts = Alert::with('area')
                             ->where('status', 'active')
                             ->orderBy('detected_at', 'desc')
                             ->take(4)
                             ->get();

        // SAFE LOCATIONS DATA MATRIX
        $totalShelters = SafeLocation::count();
        
        
        $activeShelters = SafeLocation::where('max_capacity', '>', 0)->count();

        
        $recentShelters = SafeLocation::orderBy('id', 'desc')
                                      ->take(4)
                                      ->get();

        return response()->json([
            'critical_count' => $criticalCount,
            'recent_alerts'  => $recentAlerts,
            'total_shelters' => $totalShelters,
            'active_shelters'=> $activeShelters,
            'recent_shelters'=> $recentShelters
        ], 200);
    }
}