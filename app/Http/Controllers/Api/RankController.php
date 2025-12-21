<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(
            $request->user()->can('view ranks'),
            403,
            'You are not allowed to view ranks'
        );

        return response()->json([
            Rank::latest()->get()
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Rank $rank)
    {
        abort_unless(
            $request->user()->can('create ranks'),
            403,
            'You are not allowed to create ranks'
        );

        $validated = $request->validate([
            'name' => 'required|unique:ranks,name',
            'priority' => 'required|integer|min:0|max:100',
        ]);

        $rank = Rank::create($validated);
        return response()->json($rank, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Rank $rank, Request $request)
    {
        abort_unless(
            $request->user()->can('view ranks'),
            403,
            'You are not allowed to view ranks'
        );
        return response()->json($rank, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rank $rank)
    {
        abort_unless(
            $request->user()->can('update ranks'),
            403,
            'You are not allowed to update ranks'
        );
        $validated = $request->validate([
            'name' => 'required|unique:ranks,name',
            'priority' => 'required|integer|min:0|max:100',
        ]);
        $rank->update($validated);
        return response()->json($rank, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rank $rank, Request $request  )
    {
        abort_unless(
            $request->user()->can('delete ranks'),
            403,
            'You are not allowed to delete ranks'
        );

        $rank->delete();
        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, Rank $rank)
    {
        abort_unless(
            $request->user()->can('update rank status'),
            403
        );
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['active', 'inactive', 'suspended']),
            ],
        ]);

        $rank->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Rank status updated successfully',
            'data' => $rank,
        ]);
    }
}
