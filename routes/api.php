<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\RankController;
use App\Http\Controllers\Api\BranchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Employee;
use App\Http\Controllers\EmployeeInvitationController;

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
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('departments', DepartmentController::class);
    Route::patch('departments/{department}/status', [DepartmentController::class, 'updateStatus']);

    // Custom preview route - MUST be before resource routes
    Route::get('/employees/next-code', function () {
        $last = Employee::where('employee_code', 'like', 'EMP-%')
            ->orderByRaw("CAST(SUBSTR(employee_code, 5) AS UNSIGNED) DESC")
            ->first();

        $nextNumber = $last ? ((int) substr($last->employee_code, 4)) + 1 : 1;

        return response()->json([
            'code' => 'EMP-' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT)
        ]);
    });
    Route::apiResource('employees', EmployeeController::class);
    Route::patch('employees/{employee}/status', [EmployeeController::class, 'updateStatus']);
    // routes/api.php


    Route::apiResource('ranks', RankController::class);
    Route::patch('ranks/{rank}/status', [RankController::class, 'updateStatus']);

    Route::apiResource('branches', BranchController::class);
    Route::patch('branches/{branch}/status', [BranchController::class, 'updateStatus']);

});

// routes/web.php



Route::post('/set-password/{employee}', [EmployeeInvitationController::class, 'store']);
Route::get('/invitation/{employee}', [EmployeeInvitationController::class, 'show'])
    ->name('invitation.set-password')
    ->middleware('signed');
// routes/api.php
Route::post('/employees/{employee}/send-invitation', [EmployeeInvitationController::class, 'sendInvitation'])
    ->middleware('auth:sanctum');

// routes/api.php
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);




