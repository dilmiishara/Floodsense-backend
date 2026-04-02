<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SafeLocation;
use Illuminate\Support\Facades\Validator;

class SafeLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Get all safe locations
    public function index()
    {
        $locations = SafeLocation::all();
        return response()->json($locations);
    }

    /**
     * Store a newly created resource in storage.
     */
    // Add a new safe location
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location_name' => 'required|string|max:255',
            'location_type' => 'required|string|max:50',
            'address' => 'required|string',
            'district' => 'required|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'max_capacity' => 'nullable|integer|min:0',
            'elevation_m' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location = SafeLocation::create($request->all());
        return response()->json($location, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $location = SafeLocation::find($id);
        if (!$location) {
            return response()->json(['message' => 'Safe location not found'], 404);
        }
        return response()->json($location);
    }

    /**
     * Update the specified resource in storage.
     */
    // Update safe location
    public function update(Request $request, $id)
    {
        $location = SafeLocation::find($id);
        if (!$location) {
            return response()->json(['message' => 'Safe location not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'location_name' => 'sometimes|required|string|max:255',
            'location_type' => 'sometimes|required|string|max:50',
            'address' => 'sometimes|required|string',
            'district' => 'sometimes|required|string|max:100',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'max_capacity' => 'nullable|integer|min:0',
            'elevation_m' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location->update($request->all());
        return response()->json($location);
    }

    /**
     * Remove the specified resource from storage.
     */
    // Delete safe location
    public function destroy($id)
    {
        $location = SafeLocation::find($id);
        if (!$location) {
            return response()->json(['message' => 'Safe location not found'], 404);
        }
        $location->delete();
        return response()->json(['message' => 'Safe location deleted successfully']);
    }
}
