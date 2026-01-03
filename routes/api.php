<?php

use App\Http\Controllers\Api\Attendance\AdminAttendanceController;
use App\Http\Controllers\Api\Attendance\AttendanceExportController;
use App\Http\Controllers\Api\Attendance\AttendancePunchController;
use App\Http\Controllers\Api\Attendance\AttendanceStatsController;
use App\Http\Controllers\Api\Attendance\BiometricEnrollmentController;
use App\Http\Controllers\Api\Attendance\EmployeeAttendanceController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ChatSeenController;
use App\Http\Controllers\Api\ChatTypingController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\Loan\LoanController;
use App\Http\Controllers\Api\Loan\LoanExportController;
use App\Http\Controllers\Api\Payroll\PayrollController;
use App\Http\Controllers\Api\Payroll\PayrollExportController;
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
    Route::get('/leaves/export-pdf/{type}', [LeaveRequestController::class, 'exportPdf']);
    Route::get('/leaves/export-excel/{type}', [LeaveRequestController::class, 'exportExcel']);
    // Staff
    Route::post('/leaves',     [LeaveRequestController::class, 'store']);
    Route::get('/leaves/my',   [LeaveRequestController::class, 'myLeaves']);

    // Admin / HR
    Route::get('/leaves',            [LeaveRequestController::class, 'index']);
    Route::patch('/leaves/{leave}',  [LeaveRequestController::class, 'update']);
    Route::get('/leaves/my/export-pdf/{type}', [LeaveRequestController::class, 'exportMyPdf']);
    Route::get('/leaves/my/export-excel/{type}', [LeaveRequestController::class, 'exportMyExcel']);
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

    Route::get('/payrolls/export/pdf', [PayrollExportController::class, 'pdf']);
    Route::get('/payrolls/export/excel', [PayrollExportController::class, 'excel']);
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

            Route::get('/pending-withdrawals', [WalletController::class, 'pendingWithdrawals']);
            Route::post('/process/{transaction}', [WalletController::class, 'processWithdrawal']);
            Route::post('/deposit/{employeeId}', [WalletController::class, 'manualDeposit']);

    });

    // ================= LOAN ROUTES =================
    // Employee routes
    Route::prefix('loans')->group(function () {
        Route::get('/my', [LoanController::class, 'myLoans']);            // View own loans
        Route::post('/request', [LoanController::class, 'requestLoan']);  // Request a loan

        Route::get('/my/export/pdf', [LoanExportController::class, 'myPdf']);
        Route::get('/my/export/excel', [LoanExportController::class, 'myExcel']);
        // Admin routes

        Route::get('/pending', [LoanController::class, 'pendingLoans']);            // List pending loans
        Route::post('/process/{loan}', [LoanController::class, 'processLoan']);     // Approve or reject loan
        Route::get('/export/pdf', [LoanExportController::class, 'pdf']);
        Route::get('/export/excel', [LoanExportController::class, 'excel']);
        Route::get('/history', [LoanController::class, 'history']);
        Route::get('/pending', [LoanController::class, 'pending']);

    });

});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/chat/messages', [ChatController::class, 'index']);
    Route::post('/chat/messages', [ChatController::class, 'store']);
});

Route::post('/chat/seen', [ChatSeenController::class, 'store'])
    ->middleware('auth:sanctum');

Route::post('/chat/typing', [ChatTypingController::class, 'store'])
    ->middleware('auth:sanctum');



Route::middleware('auth:sanctum')->group(function () {

    // Employee (self)
    Route::get('/employee/attendance/summary', [EmployeeAttendanceController::class, 'summary']);
    Route::get('/employee/attendance/history', [EmployeeAttendanceController::class, 'history']);
    Route::get('/employee/attendance/my/export/pdf', [AttendanceExportController::class, 'myPdf']);
    Route::get('/employee/attendance/my/export/excel', [AttendanceExportController::class, 'myExcel']);
    // Admin / HR
    Route::get('/admin/attendance/today', [AdminAttendanceController::class, 'today']);
    Route::get('/admin/attendance/report', [AdminAttendanceController::class, 'report']);
    Route::get('/admin/attendance/employee/{employee}', [AdminAttendanceController::class, 'employee']);

    // Employee (self)
    Route::get('/employee/attendance/summary', [EmployeeAttendanceController::class, 'summary']);
    Route::get('/employee/attendance/history', [EmployeeAttendanceController::class, 'history']);

    // Admin / HR - View
    Route::get('/admin/attendance/today', [AdminAttendanceController::class, 'today']);
    Route::get('/admin/attendance/report', [AdminAttendanceController::class, 'report']);
    Route::get('/admin/attendance/employee/{employee}', [AdminAttendanceController::class, 'employee']);

    // Admin / HR - Manual record (NEW)
    Route::post('/admin/attendance/record', [AdminAttendanceController::class, 'record']);
    Route::get('/admin/attendance/export/pdf', [AttendanceExportController::class, 'pdf']);
    Route::get('/admin/attendance/export/excel', [AttendanceExportController::class, 'excel']);
});

Route::middleware('auth:sanctum')->group(function () {

    // Enroll / Re-enroll
    Route::post('/biometric/enroll', [BiometricEnrollmentController::class, 'enroll'])
        ->name('api.biometric.enroll');

    // Reset biometric
    Route::post('/admin/biometric/reset', [BiometricEnrollmentController::class, 'reset'])
        ->name('api.biometric.reset');

});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/attendance/export/excel', [AttendanceExportController::class, 'excel']);
    Route::get('/admin/attendance/export/pdf', [AttendanceExportController::class, 'pdf']);
    Route::get('attendance/stats', [AttendanceStatsController::class, 'index']);

});

Route::post('/attendance/punch', [AttendancePunchController::class, 'punch']);



