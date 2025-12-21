<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Validation\ValidationException;

Route::post('/register', function(Request $request){
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8',
    ]);

    $user = User::query()->create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    return response()->json($user);
});



Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/dashboard', function (Request $request) {
    return response()->json([
        'message' => 'Welcome to your Dashboard!',
        'user' => $request->user()
    ]);
});
Route::middleware(['auth:sanctum', 'permission:view departments'])->group(function () {
    Route::apiResource('departments', DepartmentController::class);
    Route::patch('departments/{department}/status', [DepartmentController::class, 'updateStatus']);
});

Route::middleware(['auth:sanctum', 'permission:view employees'])->group(function () {
    Route::apiResource('employees', EmployeeController::class);
    Route::patch('employees/{employee}/status', [EmployeeController::class, 'updateStatus']);
});

// routes/api.php
Route::middleware('auth:sanctum')->get('/me', function () {
    $user = auth()->user();
    return response()->json([
        'user' => $user,
        'permissions' => $user->getAllPermissions()->pluck('name'), // ['create departments', 'update employees', ...]
    ]);
});



