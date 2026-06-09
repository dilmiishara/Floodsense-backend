<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use Illuminate\Http\Request;

class FCMTokenController extends Controller
{
    // Save token
    public function saveToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_type' => 'nullable|string',
        ]);

        FcmToken::updateOrCreate(
            ['token' => $request->token],
            [
                'device_type' => $request->device_type ?? 'android',
                'is_active' => true,
            ]
        );

        return response()->json([
            'message' => 'Token saved successfully'
        ], 200);
    }

    // Delete token (when notifications disabled)
    public function deleteToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        FcmToken::where('token', $request->token)
            ->update(['is_active' => false]);

        return response()->json([
            'message' => 'Token removed successfully'
        ], 200);
    }
}
