<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RankController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\EmployeeInvitationController;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Models
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| PUBLIC AUTH ROUTES
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| PASSWORD RESET (PUBLIC)
|--------------------------------------------------------------------------
*/

Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password',  [PasswordResetController::class, 'resetPassword'])
    ->name('password.reset');

/*
|--------------------------------------------------------------------------
| EMPLOYEE INVITATION (PUBLIC - SIGNED)
|--------------------------------------------------------------------------
*/

Route::get('/invitation/{employee}', [EmployeeInvitationController::class, 'show'])
    ->middleware('signed')
    ->name('invitation.set-password');

Route::post('/set-password/{employee}', [EmployeeInvitationController::class, 'store']);

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (AUTHENTICATED)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH / USER
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', fn (Request $request) => response()->json([
        'message' => 'Welcome to your Dashboard!',
        'user'    => $request->user(),
    ]));

    Route::get('/me', [AuthController::class, 'me']);

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get('/profile',  [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | DEPARTMENTS
    |--------------------------------------------------------------------------
    */

    Route::apiResource('departments', DepartmentController::class);
    Route::patch('departments/{department}/status', [DepartmentController::class, 'updateStatus']);

    /*
    |--------------------------------------------------------------------------
    | RANKS
    |--------------------------------------------------------------------------
    */

    Route::apiResource('ranks', RankController::class);
    Route::patch('ranks/{rank}/status', [RankController::class, 'updateStatus']);

    /*
    |--------------------------------------------------------------------------
    | BRANCHES
    |--------------------------------------------------------------------------
    */

    Route::apiResource('branches', BranchController::class);
    Route::patch('branches/{branch}/status', [BranchController::class, 'updateStatus']);

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEES
    |--------------------------------------------------------------------------
    */

    // Preview next employee code
    Route::get('/employees/next-code', function () {
        $last = Employee::where('employee_code', 'like', 'EMP-%')
            ->orderByRaw("CAST(SUBSTR(employee_code, 5) AS UNSIGNED) DESC")
            ->first();

        $next = $last
            ? ((int) substr($last->employee_code, 4)) + 1
            : 1;

        return response()->json([
            'code' => 'EMP-' . str_pad($next, 2, '0', STR_PAD_LEFT),
        ]);
    });

    Route::apiResource('employees', EmployeeController::class);
    Route::patch('employees/{employee}/status', [EmployeeController::class, 'updateStatus']);

    Route::post(
        '/employees/{employee}/send-invitation',
        [EmployeeInvitationController::class, 'sendInvitation']
    );

    /*
    |--------------------------------------------------------------------------
    | LEAVE MANAGEMENT
    |--------------------------------------------------------------------------
    */

    // Staff
    Route::post('/leaves',     [LeaveRequestController::class, 'store']);
    Route::get('/leaves/my',   [LeaveRequestController::class, 'myLeaves']);

    // Admin / HR
    Route::get('/leaves',            [LeaveRequestController::class, 'index']);
    Route::patch('/leaves/{leave}',  [LeaveRequestController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | PAYROLL
    |--------------------------------------------------------------------------
    */

    // Employee
    Route::get('/payrolls/my',                [PayrollController::class, 'myPayslips']);
    Route::get('/payrolls/{payroll}/download',[PayrollController::class, 'downloadPayslip']);

    // Admin / HR
    Route::get('/payrolls',      [PayrollController::class, 'index']);
    Route::post('/payrolls/run', [PayrollController::class, 'run']);

    /*
    |--------------------------------------------------------------------------
    | WALLET
    |--------------------------------------------------------------------------
    */

});

Route::middleware('auth:sanctum')->group(function () {

    // ================= WALLET ROUTES =================
    // Employee routes
    Route::prefix('wallet')->group(function () {
        Route::get('/my', [WalletController::class, 'myWallet']);                     // View own wallet
        Route::post('/withdraw', [WalletController::class, 'requestWithdrawal']);     // Request withdrawal
        Route::post('/goal', [WalletController::class, 'setGoal']);                   // Set savings goal

        // Admin & HR routes
        Route::middleware('role:admin|hr')->group(function () {
            Route::get('/pending-withdrawals', [WalletController::class, 'pendingWithdrawals']);
            Route::post('/process/{transaction}', [WalletController::class, 'processWithdrawal']);
            Route::post('/deposit/{employeeId}', [WalletController::class, 'manualDeposit']);
        });
    });

    // ================= LOAN ROUTES =================
    // Employee routes
    Route::prefix('loans')->group(function () {
        Route::get('/my', [LoanController::class, 'myLoans']);            // View own loans
        Route::post('/request', [LoanController::class, 'requestLoan']);  // Request a loan

        // Admin routes
            Route::get('/pending', [LoanController::class, 'pendingLoans']);            // List pending loans
            Route::post('/process/{loan}', [LoanController::class, 'processLoan']);     // Approve or reject loan
    });

});
