<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Safe check for user
        if (!$request->user() || !$request->user()->can('view branches')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $branches = Branch::select('id', 'name', 'state', 'country', 'status', 'created_at', 'updated_at')
            ->latest()
            ->get();

        return response()->json($branches, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->user() || !$request->user()->can('create branches')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:branches,name',
            'state' => 'required|string|max:255',
            'country' => 'sometimes|string|max:255', // optional, default Nigeria
            'status' => 'sometimes|in:active,inactive',
        ]);

        $branch = Branch::create([
            'name' => $validated['name'],
            'state' => $validated['state'],
            'country' => $validated['country'] ?? 'Nigeria',
            'status' => $validated['status'] ?? 'active',
        ]);

        return response()->json([
            'message' => 'Branch created successfully.',
            'branch' => $branch,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch, Request $request)
    {
        if (!$request->user() || !$request->user()->can('view branches')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($branch, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        if (!$request->user() || !$request->user()->can('update branches')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches')->ignore($branch->id),
            ],
            'state' => 'required|string|max:255',
            'country' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $branch->update([
            'name' => $validated['name'],
            'state' => $validated['state'],
            'country' => $validated['country'] ?? $branch->country,
            'status' => $validated['status'] ?? $branch->status,
        ]);

        return response()->json([
            'message' => 'Branch updated successfully.',
            'branch' => $branch->refresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch, Request $request)
    {
        if (!$request->user() || !$request->user()->can('delete branches')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $branch->delete();

        return response()->json(['message' => 'Branch deleted successfully.']);
    }

    /**
     * Toggle branch status
     */
    public function updateStatus(Request $request, Branch $branch)
    {
        if (!$request->user() || !$request->user()->can('update branch status')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $newStatus = $branch->status === 'active' ? 'inactive' : 'active';

        $branch->update(['status' => $newStatus]);

        return response()->json([
            'message' => 'Branch status updated successfully',
            'data' => $branch->refresh(),
        ]);
    }
}
