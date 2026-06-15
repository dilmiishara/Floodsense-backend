<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserLocationController extends Controller
{
    public function saveLocation(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        try {
            DB::table('user_locations')->updateOrInsert(
                ['fcm_token' => $request->fcm_token],
                [
                    'latitude'   => $request->latitude,
                    'longitude'  => $request->longitude,
                    'updated_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Location saved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
