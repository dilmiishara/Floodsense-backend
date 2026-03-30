<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;

use App\Http\Controllers\UserController;

// Login route
Route::post('/login', [AuthController::class, 'login']);

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