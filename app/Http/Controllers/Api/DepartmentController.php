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
    public function index()
    {   $this->authorize('view', Department::class);
        return response()->json(
            Department::latest()->get()
    );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create departments', Department::class); // checks policy  // Will throw 403 if unauthorized
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
    public function show(Department $department)
    {
        $this->authorize('view departments', $department);
        return response()->json($department);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $this->authorize('update departments', $department);
        $validated = $request->validate([
            'name' => 'required|string|unique:departments,name',
            'description' => 'required|string',
        ]);

        $department->update($validated);
        return response()->json($department, 200);
    }

    public function updateStatus(Department $department)
    {
        $this->authorize('update department status', $department);
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
    public function destroy(Department $department)
    {
        $department->delete();
        return response()->json(['message' => 'Department deleted'],);
    }
}
