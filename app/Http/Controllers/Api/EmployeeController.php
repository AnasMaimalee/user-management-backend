<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(
            $request->user()->can('view departments'),
            403
        );
        return Employee::with(['department', 'rank', 'branch'])->latest()->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Employee $employee)
    {
        abort_unless(
            $request->user()->can('create departments'),
            403
        );

        $data = $request->validate([
            'employee_code' => 'required|unique:employees',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email|unique:employees',
            'department_id' => 'required|exists:departments,id',
            'rank_id' => 'required|exists:ranks,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $employee = Employee::create($data);
        return response()->json($employee, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee, Request $request)
    {
        abort_unless(
            $request->user()->can('view departments'),
            403
        );

        return response()->json($employee, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        abort_unless(
            $request->user()->can('update departments'),
            403
        );

        $data = $request->validate([
            'employee_code' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email',
            'department_id' => 'required|exists:departments,id',
            'rank_id' => 'required|exists:ranks,id',
            'branch_id' => 'required|exists:branches,id',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $employee->update($data);
        return response()->json($employee, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee, Request $request)
    {
        abort_unless(
            $request->user()->can('delete departments'),
            403
        );

        $employee->delete();
        return response()->json(['message' => 'Employee deleted successfully.'], 200);
    }

    public function updateStatus(Request $request, Employee $employee)
    {
        abort_unless(
            $request->user()->can('update department status'),
            403
        );
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['active', 'inactive', 'suspended']),
            ],
        ]);

        $employee->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Employee status updated successfully',
            'data' => $employee,
        ]);
    }
}
