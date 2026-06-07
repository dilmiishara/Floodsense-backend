<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter; 
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // 🔐 LOGIN
    public function login(Request $request)
    {
        
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60); 

            return response()->json([
                'status' => 'locked',
                'message' => "Too many login attempts. Your administrative account has been locked for temporary security reasons. Please try again after {$minutes} minutes."
            ], 429); 
        }

        
        if (!Auth::attempt($request->only('email', 'password'))) {
            
            
            RateLimiter::hit($throttleKey, 900); 

            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid credentials. Please verify your administrative security keys.'
            ], 401);
        }

        
        $user = Auth::user();

        
        RateLimiter::clear($throttleKey);

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    // 🚪 LOGOUT
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }


    public function resetPasswordWithOtp(Request $request)
{
    
    $request->validate([
        'email'    => 'required|email|exists:users,email',
        'otp'      => 'required|integer',
        'password' => 'required|string|min:6|confirmed', 
    ]);

    
    $cachedOtp = Cache::get('reset_otp_' . $request->email);

    if (!$cachedOtp || intval($request->otp) !== intval($cachedOtp)) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Invalid or expired verification token key. Please try again.'
        ], 403);
    }

    
    $user = User::where('email', $request->email)->first();
    $user->password = Hash::make($request->password);
    $user->save();

    
    Cache::forget('reset_otp_' . $request->email);

    return response()->json([
        'status' => 'success',
        'message' => 'Your security password has been successfully re-configured!'
    ], 200);
}


public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email'], [
            'email.exists' => 'This email address is not registered in FloodSense.'
        ]);

        
        $otp = rand(100000, 999999);
        
        
        Cache::put('reset_otp_' . $request->email, $otp, 600);

        try {
            
            Mail::raw("Your FloodSense password reset verification token code is: {$otp}. This code expires in 10 minutes.", function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('FloodSense — Password Recovery Token');
            });

            return response()->json([
                'status' => 'success',
                'message' => 'A password reset verification token has been dispatched to your email.'
            ], 200);

        } catch (\Exception $e) {
            
            \Log::info("FloodSense Password Reset Verification Key for {$request->email}: {$otp}");
            
            return response()->json([
                'status' => 'success',
                'message' => 'Verification key generated. (Backup Logged).'
            ], 200);
        }
    }

}