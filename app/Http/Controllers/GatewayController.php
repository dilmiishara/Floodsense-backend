<?php

namespace App\Http\Controllers;

use App\Models\Gateway;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class GatewayController extends Controller
{
    // ── GET /api/gateways ────────────────────────────────────────────────────
    // Returns all gateways (optionally filtered by status query param)
    // Example: GET /api/gateways?status=active
    public function index(Request $request): JsonResponse
    {
        $query = Gateway::query()->orderBy('created_at', 'desc');

        if ($request->has('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('status', $request->status);
        } else {
            // Default: only active gateways
            $query->where('status', 'active');
        }

        $gateways = $query->get();

        return response()->json([
            'success' => true,
            'data'    => $gateways,
        ]);
    }

    // ── POST /api/gateways ───────────────────────────────────────────────────
    // Create a new gateway
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'lora_gateway_eui' => 'required|string|max:23|unique:gateways,lora_gateway_eui',
            'latitude'         => 'required|numeric|between:-90,90',
            'longitude'        => 'required|numeric|between:-180,180',
            'location_name'    => 'nullable|string|max:255',
            'status'           => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        // Default status to active on creation
        $validated['status'] = $validated['status'] ?? 'active';

        $gateway = Gateway::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Gateway created successfully.',
            'data'    => $gateway,
        ], 201);
    }

    // ── GET /api/gateways/{id} ───────────────────────────────────────────────
    // Get a single gateway by ID
    public function show(int $id): JsonResponse
    {
        $gateway = Gateway::find($id);

        if (!$gateway) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $gateway,
        ]);
    }

    // ── PUT /api/gateways/{id} ───────────────────────────────────────────────
    // Update a gateway's details
    public function update(Request $request, int $id): JsonResponse
    {
        $gateway = Gateway::find($id);

        if (!$gateway) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name'             => 'sometimes|required|string|max:100',
            'lora_gateway_eui' => [
                'sometimes', 'required', 'string', 'max:23',
                Rule::unique('gateways', 'lora_gateway_eui')->ignore($id),
            ],
            'latitude'         => 'sometimes|required|numeric|between:-90,90',
            'longitude'        => 'sometimes|required|numeric|between:-180,180',
            'location_name'    => 'nullable|string|max:255',
            'status'           => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        $gateway->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Gateway updated successfully.',
            'data'    => $gateway->fresh(),
        ]);
    }

    // ── DELETE /api/gateways/{id} ────────────────────────────────────────────
    // Soft-delete: sets status to 'inactive' instead of removing the record
    public function destroy(int $id): JsonResponse
    {
        $gateway = Gateway::find($id);

        if (!$gateway) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway not found.',
            ], 404);
        }

        if ($gateway->status === 'inactive') {
            return response()->json([
                'success' => false,
                'message' => 'Gateway is already inactive.',
            ], 409);
        }

        $gateway->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => "Gateway \"{$gateway->name}\" has been deactivated.",
            'data'    => $gateway->fresh(),
        ]);
    }

    // ── PATCH /api/gateways/{id}/activate ────────────────────────────────────
    // Re-activate a previously deactivated gateway
    public function activate(int $id): JsonResponse
    {
        $gateway = Gateway::find($id);

        if (!$gateway) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway not found.',
            ], 404);
        }

        $gateway->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => "Gateway \"{$gateway->name}\" has been activated.",
            'data'    => $gateway->fresh(),
        ]);
    }

    // ── PATCH /api/gateways/{id}/ping ────────────────────────────────────────
    // Update last_seen timestamp (called by IoT devices on heartbeat)
    public function ping(int $id): JsonResponse
    {
        $gateway = Gateway::find($id);

        if (!$gateway) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway not found.',
            ], 404);
        }

        $gateway->update(['last_seen' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Last seen updated.',
            'data'    => $gateway->fresh(),
        ]);
    }
}
