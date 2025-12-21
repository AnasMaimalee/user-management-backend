<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(
            $request->user()->can('view branches'),
            403
        );

        return response()->json(Branch::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Branch $branch)
    {
        abort_unless(
            $request->user()->can('create branches'),
            403
        );

        $validated = $request->validate([
            'name' => 'required|unique:branches,name',
            'city' => 'required|string',
            'country' => 'required|string',
        ]);

        $branch = Branch::create($validated);

        return response()->json([
            'message' => 'Branch created successfully.',
            'branch' => $branch,
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Branch $branch, Request $request)
    {
        abort_unless(
            $request->user()->can('view branches'),
            403
        );
        return response()->json($branch, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        abort_unless(
            $request->user->can('uodate ranks'),
            403
        );

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        $branch->update($validated);
        return response()->json([
            'message' => 'Rank updated successfully.',
            'rank' => $branch,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch, Request $request)
    {
        abort_unless(
            $request->user()->can('delete branches'),
            403
        );

        $branch->delete();
        return response()->json(['message' => 'Branch successfully deleted.']);
    }

    public function updateStatus(Request $request, Branch $branch)
    {
        abort_unless(
            $request->user()->can('update ranks status'),
            403
        );
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['active', 'inactive', 'suspended']),
            ],
        ]);

        $branch->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Branch status updated successfully',
            'data' => $branch,
        ]);
    }

}
