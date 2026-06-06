<?php

namespace App\Http\Controllers;

use App\Models\SensorNode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class SensorNodeController extends Controller
{
    // ── GET /api/sensor-nodes ─────────────────────────────────────────────────
    // Returns all nodes. Filter by status or gateway_id via query params.
    // Example: GET /api/sensor-nodes?status=active&gateway_id=1
    public function index(Request $request): JsonResponse
    {
        $query = SensorNode::with('gateway')
            ->orderBy('created_at', 'desc');

        // Default to only active nodes unless status is specified
        if ($request->has('status') && in_array($request->status, ['active', 'inactive', 'maintenance', 'all'])) {
            if ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        } else {
            $query->where('status', 'active');
        }

        if ($request->has('gateway_id')) {
            $query->where('gateway_id', $request->gateway_id);
        }

        $nodes = $query->get()->map(function ($node) {
            return [
                'id'           => $node->id,
                'name'         => $node->name,
                'latitude'     => $node->latitude,
                'longitude'    => $node->longitude,
                'status'       => $node->status,
                'lora_dev_eui' => $node->lora_dev_eui,
                'lora_app_eui' => $node->lora_app_eui,
                'last_seen'    => $node->last_seen,
                'created_at'   => $node->created_at,
                'updated_at'   => $node->updated_at,
                'gateway_id'   => $node->gateway_id,
                'gateway'      => $node->gateway ? [
                    'id'            => $node->gateway->id,
                    'name'          => $node->gateway->name,
                    'location_name' => $node->gateway->location_name,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $nodes,
        ]);
    }

    // ── POST /api/sensor-nodes ────────────────────────────────────────────────
    // Register a new sensor node
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'lora_dev_eui' => 'required|string|max:50|unique:sensor_nodes,lora_dev_eui',
            'lora_app_eui' => 'required|string|max:50',
            'gateway_id'   => 'required|integer|exists:gateways,id',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
            'status'       => ['nullable', Rule::in(['active', 'inactive', 'maintenance'])],
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        $node = SensorNode::create($validated);

        // Load with gateway relation for response
        $node->load('gateway');

        return response()->json([
            'success' => true,
            'message' => 'Sensor node registered successfully.',
            'data'    => $node,
        ], 201);
    }

    // ── GET /api/sensor-nodes/{id} ────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $node = SensorNode::with('gateway')->find($id);

        if (!$node) {
            return response()->json([
                'success' => false,
                'message' => 'Sensor node not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $node,
        ]);
    }

    // ── PUT /api/sensor-nodes/{id} ────────────────────────────────────────────
    // Update a sensor node's details
    public function update(Request $request, int $id): JsonResponse
    {
        $node = SensorNode::find($id);

        if (!$node) {
            return response()->json([
                'success' => false,
                'message' => 'Sensor node not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name'         => 'sometimes|required|string|max:100',
            'lora_dev_eui' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('sensor_nodes', 'lora_dev_eui')->ignore($id),
            ],
            'lora_app_eui' => 'sometimes|required|string|max:50',
            'gateway_id'   => 'sometimes|required|integer|exists:gateways,id',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
            'status'       => ['sometimes', Rule::in(['active', 'inactive', 'maintenance'])],
        ]);

        $node->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sensor node updated successfully.',
            'data'    => $node->fresh()->load('gateway'),
        ]);
    }

    // ── DELETE /api/sensor-nodes/{id} ─────────────────────────────────────────
    // Soft-delete: sets status to 'inactive'
    public function destroy(int $id): JsonResponse
    {
        $node = SensorNode::find($id);

        if (!$node) {
            return response()->json([
                'success' => false,
                'message' => 'Sensor node not found.',
            ], 404);
        }

        if ($node->status === 'inactive') {
            return response()->json([
                'success' => false,
                'message' => 'Sensor node is already inactive.',
            ], 409);
        }

        $node->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => "Sensor node \"{$node->name}\" has been deactivated.",
            'data'    => $node->fresh(),
        ]);
    }

    // ── PATCH /api/sensor-nodes/{id}/activate ────────────────────────────────
    public function activate(int $id): JsonResponse
    {
        $node = SensorNode::find($id);

        if (!$node) {
            return response()->json([
                'success' => false,
                'message' => 'Sensor node not found.',
            ], 404);
        }

        $node->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => "Sensor node \"{$node->name}\" has been activated.",
            'data'    => $node->fresh(),
        ]);
    }

    // ── PATCH /api/sensor-nodes/{id}/maintenance ──────────────────────────────
    public function maintenance(int $id): JsonResponse
    {
        $node = SensorNode::find($id);

        if (!$node) {
            return response()->json([
                'success' => false,
                'message' => 'Sensor node not found.',
            ], 404);
        }

        $node->update(['status' => 'maintenance']);

        return response()->json([
            'success' => true,
            'message' => "Sensor node \"{$node->name}\" set to maintenance mode.",
            'data'    => $node->fresh(),
        ]);
    }

    // ── PATCH /api/sensor-nodes/{id}/ping ────────────────────────────────────
    // Update last_seen timestamp (called by IoT device on heartbeat)
    public function ping(int $id): JsonResponse
    {
        $node = SensorNode::find($id);

        if (!$node) {
            return response()->json([
                'success' => false,
                'message' => 'Sensor node not found.',
            ], 404);
        }

        $node->update(['last_seen' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Last seen updated.',
            'data'    => $node->fresh(),
        ]);
    }
}
