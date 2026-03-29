<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of all users with relationships.
     */
    public function index()
    {
        // Fetching users with their assigned area info
        $users = User::with(['area'])->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $users
        ], 200);
    }

    /**
     * Store a newly created user in the database.
     */
    public function store(Request $request)
{
    // 1. Define Validation Rules
    $validate = Validator::make($request->all(), [
        'name'      => 'required|string|max:255',
        'email'     => 'required|email|unique:users,email',
        'password'  => 'required|min:6',
        'role'      => 'required|integer', 
        'area_id'   => 'required|exists:areas,id',
        'telephone' => 'required|string|max:15|unique:users,telephone', // ✅ FIXED: Added unique rule
    ], [
        // Custom Professional Messages
        'email.unique'       => 'This email address is already registered in FloodSense.',
        'telephone.unique'   => 'This telephone number is already assigned to another user.', // ✅ FIXED: Added custom message
        'area_id.exists'     => 'The selected monitoring area does not exist.',
        'telephone.required' => 'A contact number is required for emergency alerts.'
    ]);

    // 2. Handle Validation Failure
    if ($validate->fails()) {
        return response()->json([
            'status'  => 'failed',
            'message' => 'Validation Error!',
            'data'    => $validate->errors(),
        ], 403);
    }

    // 3. Create the User
    try {
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'area_id'   => $request->area_id,
            'telephone' => $request->telephone,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'User created successfully',
            'user'    => $user
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Server Error: Unable to create user.'
        ], 500);
    }
}

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'User not found.'
            ], 404);
        }

       $validate = Validator::make($request->all(), [
    'name'      => 'string|max:255',
    'email'     => 'email|unique:users,email,' . $id,
    'role'      => 'integer',
    'area_id'   => 'exists:areas,id',
    'telephone' => 'string|max:15',
    'password'  => 'nullable|min:6', // ✅ Added: Nullable so it's only validated if provided
], [
    'password.min' => 'The new password must be at least 6 characters long.',
]);

        if ($validate->fails()) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Update Validation Error!',
                'data'    => $validate->errors(),
            ], 403);
        }

        // Update basic info
        $user->update($request->only(['name', 'email', 'role', 'area_id', 'telephone']));

        // Handle password update separately if provided
        if ($request->has('password') && !empty($request->password)) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'User updated successfully',
            'user'    => $user
        ], 200);
    }

    /**
     * Remove the specified user.
     */
    /**
 * Remove the specified user from the system.
 */
public function destroy($id)
{
    // 1. Find the user
    $user = User::find($id);

    // 2. Check if user exists
    if (!$user) {
        return response()->json([
            'status' => 'failed', 
            'message' => 'User not found in the FloodSense records.'
        ], 404);
    }

    // 3. Security Check: Prevent deleting yourself (Highly Professional)
    if (auth()->check() && auth()->id() == $id) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Security Violation: You cannot delete your own administrative account.'
        ], 403);
    }

    try {
        // 4. Perform Delete
        $user->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'User account and access permissions revoked successfully.'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Database Error: Could not remove user. They might be linked to active incident reports.'
        ], 500);
    }
}
    public function getAreas()
    {
        return response()->json([
            'status' => 'success',
            'data'   => Area::all()
        ], 200);
    }
}