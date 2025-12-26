<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\LeaveStatusMail;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LeaveRequestController extends Controller
{

    public function index(Request $request)
    {
        abort_unless($request->user()->can('view leaves'), 403);

        return LeaveRequest::with([
            'employee.department',
            'employee.rank',
            'employee.branch',
        ])
            ->latest()
            ->get();
    }

    public function myLeaves()
    {
        return LeaveRequest::where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'resume_date' => 'required|date|after_or_equal:end_date',
        ]);

        $leave = LeaveRequest::create([
            'reason' => $request->reason,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'resume_date' => $request->resume_date,
            'user_id' => Auth()->id()
        ]);

        return response()->json([
            'message' => 'Leave Request submitted successfully.',
            'leave_request' => $leave
        ]);
    }

    public function update(Request $request, LeaveRequest $leave)
    {
        // Only allow update if status is pending
        if ($leave->status !== 'pending') {
            return response()->json([
                'message' => 'This leave request has already been processed.'
            ], 422);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string|max:255',
        ]);

        if ($request->status === 'rejected') {
            $request->validate([
                'admin_note' => 'required|string|min:10|max:255',
            ]);
        }
        $leave->update([
            'status'       => $validated['status'],
            'admin_note'   => $validated['admin_note'] ?? null,
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
        ]);

        // Send email notification
        Mail::to($leave->employee->email)->send(new LeaveStatusMail($leave));

        return response()->json([
            'message'        => 'Leave request ' . $validated['status'] . ' successfully.',
            'leave_request'  => $leave->load('employee')
        ]);
    }
}
