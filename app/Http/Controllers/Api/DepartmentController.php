<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {    abort_unless(
        $request->user()->can('view departments'),
        403,
        'You are not allowed to view departments'
    );

        return response()->json(
            Department::latest()->get()
    );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(
            $request->user()->can('create departments'),
            403,
            'You are not allowed to create departments'
        );
        $validated = $request->validate([
            'name' => 'required|string|unique:departments,name',
            'description' => 'required|string',
        ]);

        $department = Department::create($validated);
        return response()->json($department, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department, Request $request)
    {
        abort_unless(
            $request->user()->can('view departments'),
            403,
            'You are not allowed to view departments'
        );

        return response()->json($department);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        abort_unless(
            $request->user()->can('update departments'),
            403
        );
        $validated = $request->validate([
            'name' => 'required|string|unique:departments,name',
            'description' => 'required|string',
        ]);

        $department->update($validated);
        return response()->json($department, 200);
    }

    public function updateStatus(Department $department, Request $request)
    {
        abort_unless(
            $request->user()->can('update departments'),
            403
        );
        // Toggle the status
        $newStatus = $department->status === 'active' ? 'inactive' : 'active';

        $department->update(['status' => $newStatus]);

        // Refresh the model to get updated values (optional but safe)
        $department->refresh();

        return response()->json([
            'message' => "Department has been " . ($newStatus === 'active' ? 'activated' : 'deactivated') . " successfully!",
            'data'    => $department,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department, Request $request)
    {
        abort_unless(
            $request->user()->can('delete departments'),
            403
        );
        $department->delete();
        return response()->json(['message' => 'Department deleted'],);
    }
}
