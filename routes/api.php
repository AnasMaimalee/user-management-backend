<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\Employee;

/*
|--------------------------------------------------------------------------
| AUTH CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\EmployeeInvitationController;

/*
|--------------------------------------------------------------------------
| CORE CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\RankController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\EmployeeController;

/*
|--------------------------------------------------------------------------
| HR / EMPLOYEE OPERATIONS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\LoanController;

/*
|--------------------------------------------------------------------------
| ATTENDANCE
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\Attendance\EmployeeAttendanceController;
use App\Http\Controllers\Api\Attendance\AdminAttendanceController;
use App\Http\Controllers\Api\Attendance\AttendanceExportController;
use App\Http\Controllers\Api\Attendance\BiometricEnrollmentController;

/*
|--------------------------------------------------------------------------
| CHAT
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ChatSeenController;
use App\Http\Controllers\Api\ChatTypingController;

/*
|--------------------------------------------------------------------------
| PUBLIC AUTH ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/reset-password',  [PasswordResetController::class, 'resetPassword'])
        ->name('password.reset');
});

/*
|--------------------------------------------------------------------------
| EMPLOYEE INVITATION (SIGNED)
|--------------------------------------------------------------------------
*/
Route::get('/invitation/{employee}', [EmployeeInvitationController::class, 'show'])
    ->middleware('signed')
    ->name('invitation.set-password');

Route::post('/set-password/{employee}', [EmployeeInvitationController::class, 'store']);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH / USER
    |--------------------------------------------------------------------------
    */
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard', fn (Request $request) => response()->json([
        'message' => 'Welcome to your Dashboard!',
        'user'    => $request->user(),
    ]));

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */
    Route::get('/profile',  [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | MASTER DATA
    |--------------------------------------------------------------------------
    */
    Route::apiResource('departments', DepartmentController::class);
    Route::patch('departments/{department}/status', [DepartmentController::class, 'updateStatus']);

    Route::apiResource('ranks', RankController::class);
    Route::patch('ranks/{rank}/status', [RankController::class, 'updateStatus']);

    Route::apiResource('branches', BranchController::class);
    Route::patch('branches/{branch}/status', [BranchController::class, 'updateStatus']);

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEES
    |--------------------------------------------------------------------------
    */
    Route::get('/employees/next-code', function () {
        $last = Employee::where('employee_code', 'like', 'EMP-%')
            ->orderByRaw("CAST(SUBSTR(employee_code, 5) AS UNSIGNED) DESC")
            ->first();

        $next = $last ? ((int) substr($last->employee_code, 4)) + 1 : 1;

        return response()->json([
            'code' => 'EMP-' . str_pad($next, 2, '0', STR_PAD_LEFT),
        ]);
    });

    Route::apiResource('employees', EmployeeController::class);
    Route::patch('employees/{employee}/status', [EmployeeController::class, 'updateStatus']);
    Route::post('/employees/{employee}/send-invitation', [EmployeeInvitationController::class, 'sendInvitation']);

    /*
    |--------------------------------------------------------------------------
    | LEAVE MANAGEMENT
    |--------------------------------------------------------------------------
    */
    Route::prefix('leaves')->group(function () {
        Route::post('/', [LeaveRequestController::class, 'store']);     // Employee
        Route::get('/my', [LeaveRequestController::class, 'myLeaves']); // Employee

        Route::get('/', [LeaveRequestController::class, 'index']);      // Admin / HR
        Route::patch('/{leave}', [LeaveRequestController::class, 'update']);
    });

    /*
    |--------------------------------------------------------------------------
    | PAYROLL
    |--------------------------------------------------------------------------
    */
    Route::prefix('payrolls')->group(function () {
        Route::get('/my', [PayrollController::class, 'myPayslips']); // Employee
        Route::get('/{payroll}/download', [PayrollController::class, 'downloadPayslip']);

        Route::get('/', [PayrollController::class, 'index']);       // Admin / HR
        Route::post('/run', [PayrollController::class, 'run']);
    });

    /*
    |--------------------------------------------------------------------------
    | WALLET
    |--------------------------------------------------------------------------
    */
    Route::prefix('wallet')->group(function () {
        Route::get('/my', [WalletController::class, 'myWallet']);
        Route::post('/withdraw', [WalletController::class, 'requestWithdrawal']);
        Route::post('/goal', [WalletController::class, 'setGoal']);

        Route::get('/pending-withdrawals', [WalletController::class, 'pendingWithdrawals']);
        Route::post('/process/{transaction}', [WalletController::class, 'processWithdrawal']);
        Route::post('/deposit/{employee}', [WalletController::class, 'manualDeposit']);
    });

    /*
    |--------------------------------------------------------------------------
    | LOANS
    |--------------------------------------------------------------------------
    */
    Route::prefix('loans')->group(function () {
        Route::get('/my', [LoanController::class, 'myLoans']);
        Route::post('/request', [LoanController::class, 'requestLoan']);

        Route::get('/pending', [LoanController::class, 'pendingLoans']);
        Route::post('/process/{loan}', [LoanController::class, 'processLoan']);
    });

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE
    |--------------------------------------------------------------------------
    */
    Route::prefix('employee/attendance')->group(function () {
        Route::get('/summary', [EmployeeAttendanceController::class, 'summary']);
        Route::get('/history', [EmployeeAttendanceController::class, 'history']);
    });

    Route::prefix('admin/attendance')->group(function () {
        Route::get('/today', [AdminAttendanceController::class, 'today']);
        Route::get('/report', [AdminAttendanceController::class, 'report']);
        Route::get('/employee/{employee}', [AdminAttendanceController::class, 'employee']);
        Route::post('/record', [AdminAttendanceController::class, 'record'])
            ->middleware('role:admin|hr');
    });

    Route::post('/biometric/enroll', [BiometricEnrollmentController::class, 'enroll'])
        ->name('api.biometric.enroll');

    /*
    |--------------------------------------------------------------------------
    | CHAT
    |--------------------------------------------------------------------------
    */
    Route::prefix('chat')->group(function () {
        Route::get('/messages', [ChatController::class, 'index']);
        Route::post('/messages', [ChatController::class, 'store']);
        Route::post('/seen', [ChatSeenController::class, 'store']);
        Route::post('/typing', [ChatTypingController::class, 'store']);
    });
});
