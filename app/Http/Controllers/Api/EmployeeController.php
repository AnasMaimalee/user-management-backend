<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\EmployeeWelcomeMail;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->can('view employees'), 403);

        return Employee::with(['department', 'rank', 'branch'])
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('create employees'), 403);

        $data = $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'email'        => 'required|email|unique:employees,email|unique:users,email',
            'department_id'=> 'nullable|exists:departments,id',
            'rank_id'      => 'nullable|exists:ranks,id',
            'branch_id'    => 'nullable|exists:branches,id',
            'role'         => 'required|string|max:255',
            'basic_salary' => 'nullable|numeric|min:0', // ← Add these
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'monthly_savings' => 'nullable|numeric|min:0',
        ]);

        $employee = Employee::create($data);

        if ($employee->email) {
            $plainPassword = Str::random(12);

            User::create([
                'id' => (string) Str::uuid(),
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'email' => $employee->email,
                'password' => Hash::make($plainPassword),
                'role' => $employee->role,
                'employee_id' => $employee->id,
            ]);
            // Pass the plain password to the mailable
            Mail::to($employee->email)->send(
                new EmployeeWelcomeMail($employee, $plainPassword)
            );

        }

        return response()->json([
            'message'  => 'Employee created successfully. ' .
                ($employee->email ? 'Login credentials sent.' : ''),
            'employee' => $employee->load(['department', 'rank', 'branch'])
        ], 201);
    }

    public function show(Employee $employee, Request $request)
    {
        abort_unless($request->user()->can('view employees'), 403);

        return response()->json(
            $employee->load(['department', 'rank', 'branch'])
        );
    }

    public function update(Request $request, Employee $employee)
    {
        abort_unless($request->user()->can('update employees'), 403);

        $validated = $request->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => [
                'required',
                'email',
                Rule::unique('employees', 'email')->ignore($employee->id),
                Rule::unique('users', 'email')->ignore($employee->user?->id),
            ],
            'department_id' => 'nullable|exists:departments,id',
            'rank_id'       => 'nullable|exists:ranks,id',
            'branch_id'     => 'nullable|exists:branches,id',
            'status'        => 'required|in:active,inactive',
        ]);

        // CORRECT: Update existing employee
        $employee->update($validated);

        // Optional: Only resend credentials if email changed and no user exists
        // (Or add separate "Resend Credentials" button in frontend)

        return response()->json([
            'message'  => 'Employee updated successfully.',
            'employee' => $employee->load(['department', 'rank', 'branch'])
        ]);
    }

    public function destroy(Employee $employee, Request $request)
    {
        abort_unless($request->user()->can('delete employees'), 403);

        // Optional: Delete linked user account too
        if ($employee->user) {
            $employee->user->delete();
        }

        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully.'], 200);
    }

    public function updateStatus(Request $request, Employee $employee)
    {
        abort_unless($request->user()->can('update employee status'), 403);

        $newStatus = $employee->status === 'active' ? 'inactive' : 'active';

        $employee->update(['status' => $newStatus]);

        return response()->json([
            'message' => 'Employee status updated successfully',
            'data'    => $employee->refresh()
        ]);
    }
}
