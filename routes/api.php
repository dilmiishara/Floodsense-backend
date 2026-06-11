<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\Api\AlertThresholdController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SafeLocationController;
use App\Http\Controllers\SensorNodeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Login route
Route::post('/login', [AuthController::class, 'login']);


Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [AuthController::class, 'resetPasswordWithOtp']);



// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/dashboard', function (Request $request) {
        return $request->user();
    });

});

Route::middleware(['auth:sanctum', 'role:1'])->group(function () {

    Route::get('/admin/dashboard', function () {
        return response()->json([
            'message' => 'Admin Dashboard'
        ]);
    });

});

// Admin routes
Route::middleware(['auth:sanctum', 'role:1'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return "Admin Only Dashboard";
    });
});

// Officer routes
Route::middleware(['auth:sanctum', 'role:2'])->group(function () {
    Route::get('/officer/dashboard', function () {
        return "Technical Officer Dashboard";
    });
});

//update user profile
Route::middleware('auth:sanctum')->group(function () {
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
});



Route::get('/test', function () {
    return response()->json([
        'message' => 'API working'
    ]);
});


//Manage User Routes
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::get('/areas', [UserController::class, 'getAreas']);
Route::get('/roles', [UserController::class, 'getRoles']);

// Manage Reports
Route::get('/reports', [ReportController::class, 'index']);      
Route::post('/reports', [ReportController::class, 'store']);
Route::delete('/reports/{id}', [ReportController::class, 'destroy']); 

//Manage Alerts
Route::get('/alerts/active', [AlertController::class, 'getActiveAlerts']);
Route::get('/alerts/history', [AlertController::class, 'getAlertHistory']);
Route::post('/alerts/generate', [AlertController::class, 'store']);
Route::put('/alerts/{id}/resolve', [AlertController::class, 'resolve']);


//manage Dashboard
Route::get('/dashboard/master-telemetry', [DashboardController::class, 'getMasterDashboardData']);

// This allows Postman to "talk" to your controller
Route::post('/sensor-data', [SensorDataController::class, 'store']);

// Get all thresholds for the table
Route::get('/alert-thresholds', [AlertThresholdController::class, 'index']);

// Update or Create a threshold
Route::post('/alert-thresholds', [AlertThresholdController::class, 'store']);

// Manage Safe locations
Route::prefix('safe-locations')->group(function () {
    Route::get('/', [SafeLocationController::class, 'index']);       // Get all
    Route::post('/', [SafeLocationController::class, 'store']);      // Add new
    Route::get('/{id}', [SafeLocationController::class, 'show']);    // Get single
    Route::put('/{id}', [SafeLocationController::class, 'update']);  // Update
    Route::delete('/{id}', [SafeLocationController::class, 'destroy']); // Delete
});

Route::get('/field-officers', [UserController::class, 'getFieldOfficers']);

// Settings routes
Route::get('/settings/{section}',  [SettingsController::class, 'show']);
Route::post('/settings/{section}', [SettingsController::class, 'update']);

// ── Gateway CRUD ──────────────────────────────────────────────────────────────
Route::prefix('gateways')->group(function () {

    Route::get('/',           [GatewayController::class, 'index']);    // GET    /api/gateways
    Route::post('/',          [GatewayController::class, 'store']);    // POST   /api/gateways
    Route::get('/{id}',       [GatewayController::class, 'show']);     // GET    /api/gateways/{id}
    Route::put('/{id}',       [GatewayController::class, 'update']);   // PUT    /api/gateways/{id}
    Route::delete('/{id}',    [GatewayController::class, 'destroy']);  // DELETE /api/gateways/{id}  → sets inactive

    // Extra actions
    Route::patch('/{id}/activate', [GatewayController::class, 'activate']); // PATCH /api/gateways/{id}/activate
    Route::patch('/{id}/ping',     [GatewayController::class, 'ping']);      // PATCH /api/gateways/{id}/ping
});


// ── Sensor Node CRUD ──────────────────────────────────────────────────────────
Route::prefix('sensor-nodes')->group(function () {

    Route::get('/', [SensorNodeController::class, 'index']);   // GET    /api/sensor-nodes
    Route::post('/', [SensorNodeController::class, 'store']);   // POST   /api/sensor-nodes
    Route::get('/{id}', [SensorNodeController::class, 'show']);  // GET    /api/sensor-nodes/{id}
    Route::put('/{id}', [SensorNodeController::class, 'update']);// PUT    /api/sensor-nodes/{id}
    Route::delete('/{id}', [SensorNodeController::class, 'destroy']); // DELETE /api/sensor-nodes/{id} → inactive

    // Extra status actions
    Route::patch('/{id}/activate', [SensorNodeController::class, 'activate']);    // PATCH /api/sensor-nodes/{id}/activate
    Route::patch('/{id}/maintenance', [SensorNodeController::class, 'maintenance']); // PATCH /api/sensor-nodes/{id}/maintenance
    Route::patch('/{id}/ping', [SensorNodeController::class, 'ping']);        // PATCH /api/sensor-nodes/{id}/ping
});

use App\Http\Controllers\FloodChartController;

Route::get('/water-level-history/{station}', [FloodChartController::class, 'history']);
Route::get('/water-level-predictions/{station}', [FloodChartController::class, 'predictions']);

