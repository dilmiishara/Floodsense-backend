<?php

namespace App\Http\Controllers;

use App\Models\MobileUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class MobileAuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required_without:phone|email|unique:mobile_users,email|nullable',
            'phone' => 'required_without:email|string|unique:mobile_users,phone|nullable',
            'password' => 'required|string|min:6|confirmed',
            'district' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $user = MobileUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'district' => $request->district,
            'city' => $request->city,
            'address' => $request->address,
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'district' => $user->district,
                'city' => $user->city,
                'address' => $user->address,
            ],
            'token' => $token,
        ], 201);
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by email or phone
        $user = MobileUser::where('email', $request->login)
            ->orWhere('phone', $request->login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Delete old tokens
        $user->tokens()->delete();

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'district' => $user->district,
                'city' => $user->city,
                'address' => $user->address,
            ],
            'token' => $token,
        ], 200);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    // Get profile
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ], 200);
    }

    // Forgot password — send OTP to email
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:mobile_users,email',
        ]);

        $user = MobileUser::where('email', $request->email)->first();

        // Generate 6 digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Save OTP — expires in 10 minutes
        $user->update([
            'otp' => Hash::make($otp),
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Send OTP via email
        Mail::raw(
            "Your FloodSense password reset OTP is: $otp\n\nThis OTP expires in 10 minutes.\n\nIf you did not request this, please ignore this email.",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('FloodSense Password Reset OTP');
            }
        );

        return response()->json([
            'message' => 'OTP sent to your email'
        ], 200);
    }

    // Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:mobile_users,email',
            'otp' => 'required|string',
        ]);

        $user = MobileUser::where('email', $request->email)->first();

        // Check OTP expired
        if (!$user->otp_expires_at || Carbon::now()->isAfter($user->otp_expires_at)) {
            return response()->json([
                'message' => 'OTP has expired. Please request a new one.'
            ], 400);
        }

        // Check OTP correct
        if (!Hash::check($request->otp, $user->otp)) {
            return response()->json([
                'message' => 'Invalid OTP'
            ], 400);
        }

        return response()->json([
            'message' => 'OTP verified successfully'
        ], 200);
    }

    // Reset password with OTP
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:mobile_users,email',
            'otp' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = MobileUser::where('email', $request->email)->first();

        // Check OTP expired
        if (!$user->otp_expires_at || Carbon::now()->isAfter($user->otp_expires_at)) {
            return response()->json([
                'message' => 'OTP has expired. Please request a new one.'
            ], 400);
        }

        // Check OTP correct
        if (!Hash::check($request->otp, $user->otp)) {
            return response()->json([
                'message' => 'Invalid OTP'
            ], 400);
        }

        // Reset password
        $user->update([
            'password' => Hash::make($request->password),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Password reset successful. You can now login.'
        ], 200);
    }

    // Update profile
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|unique:mobile_users,phone,' . $request->user()->id,
            'district' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $user = $request->user();

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'district' => $request->district,
            'city' => $request->city,
            'address' => $request->address,
        ]);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'district' => $user->district,
                'city' => $user->city,
                'address' => $user->address,
            ],
        ], 200);
    }
}
