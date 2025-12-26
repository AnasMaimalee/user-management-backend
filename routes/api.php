<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Employee;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\RankController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\EmployeeInvitationController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
/*
|--------------------------------------------------------------------------
| Public Auth Routes
|--------------------------------------------------------------------------
*/


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Auth)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Auth / Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', fn (Request $request) => response()->json([
        'message' => 'Welcome to your Dashboard!',
        'user'    => $request->user(),
    ]));

    Route::get('/me', [AuthController::class, 'me']);

    /*
    |--------------------------------------------------------------------------
    | Departments
    |--------------------------------------------------------------------------
    */

    Route::apiResource('departments', DepartmentController::class);
    Route::patch('departments/{department}/status', [DepartmentController::class, 'updateStatus']);

    /*
    |--------------------------------------------------------------------------
    | Employees
    |--------------------------------------------------------------------------
    */

    // Preview next employee code
    Route::get('/employees/next-code', function () {
        $last = Employee::where('employee_code', 'like', 'EMP-%')
            ->orderByRaw("CAST(SUBSTR(employee_code, 5) AS UNSIGNED) DESC")
            ->first();

        $nextNumber = $last
            ? ((int) substr($last->employee_code, 4)) + 1
            : 1;

        return response()->json([
            'code' => 'EMP-' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT),
        ]);
    });

    Route::apiResource('employees', EmployeeController::class);
    Route::patch('employees/{employee}/status', [EmployeeController::class, 'updateStatus']);

    // Send invitation email
    Route::post(
        '/employees/{employee}/send-invitation',
        [EmployeeInvitationController::class, 'sendInvitation']
    );

    /*
    |--------------------------------------------------------------------------
    | Ranks
    |--------------------------------------------------------------------------
    */

    Route::apiResource('ranks', RankController::class);
    Route::patch('ranks/{rank}/status', [RankController::class, 'updateStatus']);

    /*
    |--------------------------------------------------------------------------
    | Branches
    |--------------------------------------------------------------------------
    */

    Route::apiResource('branches', BranchController::class);
    Route::patch('branches/{branch}/status', [BranchController::class, 'updateStatus']);

    /*
    |--------------------------------------------------------------------------
    | Leave Management
    |--------------------------------------------------------------------------
    */

    // ===== Staff =====
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/leaves', [LeaveRequestController::class, 'store']);
        Route::get('/leaves/my', [LeaveRequestController::class, 'myLeaves']);
    });

    // ===== Admin & HR =====
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/leaves', [LeaveRequestController::class, 'index']);
        Route::patch('/leaves/{leave}', [LeaveRequestController::class, 'update']);
    });
});

/*
|--------------------------------------------------------------------------
| Invitation & Password Setup (Public)
|--------------------------------------------------------------------------
*/

Route::get('/invitation/{employee}', [EmployeeInvitationController::class, 'show'])
    ->middleware('signed')
    ->name('invitation.set-password');

Route::post('/set-password/{employee}', [EmployeeInvitationController::class, 'store']);


// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
});

// routes/api.php
// routes/api.php
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');

// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile/image', [ProfileController::class, 'updateImage']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);
});
