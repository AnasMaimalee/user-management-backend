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
        abort_unless($request->user()->can('view leave request'), 403);

        return LeaveRequest::with(['employee', 'department', 'rank', 'branch'])
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
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string|min:5|max:255',
        ]);

        $leave->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Mail::to($leave->employee->email)
            ->send(new LeaveStatusMail($leave));

        return response()->json([
            'message' => 'Leave Status updated successfully.',
            'leave_request' => $leave
        ]);
    }
}
